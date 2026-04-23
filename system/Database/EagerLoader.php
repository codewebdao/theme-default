<?php

namespace System\Database;

use System\Database\Query\Builder;

/**
 * EagerLoader - Handles eager loading of relationships
 * 
 * Prevents N+1 query problem by loading relationships in batch
 * 
 * Features:
 * - Batch relationship loading
 * - Nested relationship support
 * - Constraint support
 * - Performance optimization
 * 
 * @package System\Database
 */
class EagerLoader
{
    /** @var array<string, array> Eager load constraints */
    protected $constraints = [];

    /** @var array<string, callable> Relationship constraints */
    protected $relationshipConstraints = [];

    /** @var array<string, string> Relationship mappings */
    protected $relationshipMappings = [];

    /**
     * Add a relationship to eager load.
     * 
     * Supports Laravel-style column selection:
     * - 'author' - load all columns
     * - 'author:id,name,avatar' - load only specified columns
     * 
     * @param string $relationship Relationship name (with optional :columns)
     * @param callable|null $constraints Optional constraints
     * @return $this
     */
    public function add($relationship, callable $constraints = null)
    {
        // Parse column selection syntax: 'author:id,name,avatar'
        $columns = null;
        if (strpos($relationship, ':') !== false) {
            list($relationship, $columnString) = explode(':', $relationship, 2);
            $columns = array_map('trim', explode(',', $columnString));
        }

        // If columns specified, wrap constraints to add select()
        if ($columns !== null) {
            $originalConstraints = $constraints;
            $constraints = function ($query) use ($columns, $originalConstraints) {
                $query->select(...$columns);
                if ($originalConstraints) {
                    $originalConstraints($query);
                }
            };
        }
        $this->constraints[$relationship] = $constraints;
        return $this;
    }

    /**
     * Add multiple relationships to eager load.
     * 
     * Supports multiple formats:
     * - ['comments', 'tags'] - indexed array of strings
     * - [['comments', $closure], ['tags', $closure]] - indexed array of arrays
     * - ['comments' => $closure, 'tags' => $closure] - associative array (Laravel format)
     * 
     * @param array $relationships Array of relationship names
     * @return $this
     */
    public function addMany(array $relationships)
    {
        foreach ($relationships as $key => $value) {
            // Format: ['comments' => function($q) { ... }] (associative array)
            if (is_string($key) && is_callable($value)) {
                $this->add($key, $value);
            }
            // Format: ['comments', 'tags'] (indexed array of strings)
            elseif (is_string($value)) {
                $this->add($value);
            }
            // Format: [['comments', $closure], ['tags', $closure]] (indexed array of arrays)
            elseif (is_array($value) && count($value) === 2) {
                $this->add($value[0], $value[1]);
            }
        }
        return $this;
    }

    /**
     * Add nested relationship constraints.
     * 
     * @param string $relationship Parent relationship
     * @param callable $callback Nested constraints
     * @return $this
     */
    public function addNested($relationship, callable $callback)
    {
        $this->relationshipConstraints[$relationship] = $callback;
        return $this;
    }

    /**
     * Load relationships for a collection of models.
     * 
     * @param array $models Collection of model data
     * @param string $modelClass Model class name
     * @return array Models with relationships loaded
     */
    public function load(array $models, $modelClass)
    {
        if (empty($models) || empty($this->constraints)) {
            return $models;
        }

        $modelInstance = new $modelClass();

        foreach ($this->constraints as $relationship => $constraints) {
            $this->loadRelationship($models, $modelInstance, $relationship, $constraints);
        }

        return $models;
    }

    /**
     * Load a specific relationship for models.
     * 
     * @param array $models Collection of model data
     * @param mixed $modelInstance Model instance
     * @param string $relationship Relationship name
     * @param callable|null $constraints Optional constraints
     */
    protected function loadRelationship(array &$models, $modelInstance, $relationship, callable $constraints = null)
    {
        if (!method_exists($modelInstance, $relationship)) {
            return;
        }

        // Get relationship instance
        $relation = $modelInstance->$relationship();

        // Get foreign keys based on relationship type
        $foreignKey = $this->getForeignKey($relation);
        $localKey = $this->getLocalKey($relation);

        // Extract local key values
        $localValues = $this->extractLocalValues($models, $localKey);

        if (empty($localValues)) {
            return;
        }

        // Build query for related models
        $query = $relation->getQuery();

        // Apply constraints if provided
        if ($constraints) {
            $constraints($query);
        }

        // Apply relationship constraints
        if (isset($this->relationshipConstraints[$relationship])) {
            $this->relationshipConstraints[$relationship]($query);
        }

        // Load related models
        $relatedModels = $query->whereIn($foreignKey, $localValues)->get();

        // Group related models by foreign key
        $groupedRelated = $this->groupByForeignKey($relatedModels, $foreignKey);

        // Attach related models to parent models
        $this->attachRelatedModels($models, $groupedRelated, $relationship, $localKey, $foreignKey);
    }

    /**
     * Get foreign key from relationship.
     * 
     * @param mixed $relation Relationship instance
     * @return string
     */
    protected function getForeignKey($relation)
    {
        // Try to get foreign key from relationship
        if (method_exists($relation, 'getForeignKey')) {
            return $relation->getForeignKey();
        }

        // Try to access foreignKey property directly
        $reflection = new \ReflectionClass($relation);

        if ($reflection->hasProperty('foreignKey')) {
            $property = $reflection->getProperty('foreignKey');
            $property->setAccessible(true);
            return $property->getValue($relation);
        }

        // Default foreign key based on relationship type
        $className = $reflection->getShortName();

        switch ($className) {
            case 'HasOne':
            case 'HasMany':
                return 'user_id'; // Default for hasOne/hasMany
            case 'BelongsTo':
                return 'id'; // Default for belongsTo
            case 'BelongsToMany':
                return 'role_id'; // Default for belongsToMany
            case 'MorphOne':
            case 'MorphMany':
                return 'commentable_id'; // Default for morph relationships
            default:
                return 'id';
        }
    }

    /**
     * Get local key from relationship.
     * 
     * @param mixed $relation Relationship instance
     * @return string
     */
    protected function getLocalKey($relation)
    {
        // Try to get local key from relationship
        if (method_exists($relation, 'getLocalKey')) {
            return $relation->getLocalKey();
        }

        // Try to access localKey property directly
        $reflection = new \ReflectionClass($relation);

        if ($reflection->hasProperty('localKey')) {
            $property = $reflection->getProperty('localKey');
            $property->setAccessible(true);
            return $property->getValue($relation);
        }

        // Default local key based on relationship type
        $className = $reflection->getShortName();

        switch ($className) {
            case 'HasOne':
            case 'HasMany':
                return 'id'; // Default for hasOne/hasMany
            case 'BelongsTo':
                return 'user_id'; // Default for belongsTo
            case 'BelongsToMany':
                return 'id'; // Default for belongsToMany
            case 'MorphOne':
            case 'MorphMany':
                return 'id'; // Default for morph relationships
            default:
                return 'id';
        }
    }

    /**
     * Extract local key values from models.
     * 
     * @param array $models Collection of models
     * @param string $localKey Local key name
     * @return array
     */
    protected function extractLocalValues(array $models, $localKey)
    {
        $values = [];

        foreach ($models as $model) {
            if (isset($model[$localKey])) {
                $values[] = $model[$localKey];
            }
        }

        return array_unique($values);
    }

    /**
     * Group related models by foreign key.
     * 
     * @param array $relatedModels Related models
     * @param string $foreignKey Foreign key name
     * @return array
     */
    protected function groupByForeignKey(array $relatedModels, $foreignKey)
    {
        $grouped = [];

        foreach ($relatedModels as $model) {
            $key = $model[$foreignKey] ?? null;
            if ($key !== null) {
                $grouped[$key][] = $model;
            }
        }

        return $grouped;
    }

    /**
     * Attach related models to parent models.
     * 
     * @param array $models Parent models
     * @param array $groupedRelated Grouped related models
     * @param string $relationship Relationship name
     * @param string $localKey Local key name
     * @param string $foreignKey Foreign key name
     */
    protected function attachRelatedModels(array &$models, array $groupedRelated, $relationship, $localKey, $foreignKey)
    {
        foreach ($models as &$model) {
            $localValue = $model[$localKey] ?? null;

            if ($localValue !== null && isset($groupedRelated[$localValue])) {
                $related = $groupedRelated[$localValue];

                // For hasOne relationships, take first item
                // For hasMany relationships, take all items
                $model[$relationship] = count($related) === 1 ? $related[0] : $related;
            } else {
                $model[$relationship] = null;
            }
        }
    }

    /**
     * Get all constraints.
     * 
     * @return array
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * Clear all constraints.
     * 
     * @return $this
     */
    public function clear()
    {
        $this->constraints = [];
        $this->relationshipConstraints = [];
        $this->relationshipMappings = [];
        return $this;
    }

    /**
     * Check if eager loader has constraints.
     * 
     * @return bool
     */
    public function hasConstraints()
    {
        return !empty($this->constraints);
    }
}
