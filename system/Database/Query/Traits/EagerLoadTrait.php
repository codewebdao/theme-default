<?php
namespace System\Database\Query\Traits;

use System\Database\EagerLoader;

/**
 * EagerLoadTrait - Adds eager loading capabilities to Builder
 * 
 * Features:
 * - with() method for eager loading
 * - Nested relationship support
 * - Constraint support
 * - Performance optimization
 * 
 * @package System\Database\Query\Traits
 */
trait EagerLoadTrait
{

    /**
     * Eager load relationships.
     * 
     * @param string|array $relationships Relationship name(s) to load
     * @param callable|null $constraints Optional constraints
     * @return $this
     */
    public function with($relationships, callable $constraints = null)
    {
        if ($this->eagerLoader === null) {
            $this->eagerLoader = new EagerLoader();
        }

        if (is_string($relationships)) {
            $this->eagerLoader->add($relationships, $constraints);
        } elseif (is_array($relationships)) {
            $this->eagerLoader->addMany($relationships);
        }

        return $this;
    }

    /**
     * Eager load nested relationships.
     * 
     * @param string $relationship Parent relationship
     * @param callable $callback Nested constraints
     * @return $this
     */
    public function withNested($relationship, callable $callback)
    {
        if ($this->eagerLoader === null) {
            $this->eagerLoader = new EagerLoader();
        }

        $this->eagerLoader->addNested($relationship, $callback);
        return $this;
    }

    /**
     * Load relationships for results.
     * 
     * @param array $results Query results
     * @return array Results with relationships loaded
     */
    protected function loadRelationships(array $results)
    {
        if ($this->eagerLoader === null || !$this->eagerLoader->hasConstraints()) {
            return $results;
        }

        if ($this->model === null) {
            return $results;
        }

        // Create model instance to access relationship methods
        $modelInstance = new $this->model();
        
        // Load each relationship
        foreach ($this->eagerLoader->getConstraints() as $relationship => $constraints) {
            $this->loadSingleRelationship($results, $modelInstance, $relationship, $constraints);
        }

        return $results;
    }

    /**
     * Load a single relationship for results.
     * 
     * @param array $results Query results
     * @param mixed $modelInstance Model instance
     * @param string $relationship Relationship name
     * @param callable|null $constraints Optional constraints
     */
    protected function loadSingleRelationship(array &$results, $modelInstance, $relationship, callable $constraints = null)
    {
        if (!method_exists($modelInstance, $relationship)) {
            return;
        }

        // Get relationship instance
        $relation = $modelInstance->$relationship();
        
        // Get relationship type and keys
        $relationType = $this->getRelationType($relation);
        
        // Handle BelongsToMany separately (it's complex!)
        if ($relationType === 'belongsToMany') {
            $this->loadBelongsToManyRelationship($results, $relation, $relationship, $constraints);
            return;
        }
        
        $foreignKey = $this->getRelationForeignKey($relation, $relationType);
        $localKey = $this->getRelationLocalKey($relation, $relationType);
        
        // Extract local key values from results
        $localValues = $this->extractLocalValues($results, $localKey);
        
        if (empty($localValues)) {
            // Set empty relationship for all results
            foreach ($results as &$result) {
                $result[$relationship] = $relationType === 'hasOne' ? null : [];
            }
            return;
        }

        // Build query for related models
        $query = $relation->getQuery();
        
        // Apply constraints if provided
        if ($constraints) {
            $constraints($query);
        }

        // For BelongsTo, we need to use ownerKey for the WHERE IN clause on related table
        // For other relations, use foreignKey
        $queryKey = $foreignKey;
        if ($relationType === 'belongsTo' && method_exists($relation, 'getOwnerKey')) {
            $queryKey = $relation->getOwnerKey();
        }

        // Load related models
        $relatedModels = $query->whereIn($queryKey, $localValues)->get();
        
        // Group related models by the key we queried on
        $groupedRelated = $this->groupByForeignKey($relatedModels, $queryKey);
        
        // Attach related models to parent models
        $this->attachRelatedModels($results, $groupedRelated, $relationship, $localKey, $queryKey, $relationType);
    }

    /**
     * Load BelongsToMany relationship (special handling for pivot tables)
     * 
     * @param array $results Query results
     * @param mixed $relation Relationship instance
     * @param string $relationship Relationship name
     * @param callable|null $constraints Optional constraints
     */
    protected function loadBelongsToManyRelationship(array &$results, $relation, $relationship, callable $constraints = null)
    {
        $reflection = new \ReflectionClass($relation);
        
        // Get pivot table and keys
        $pivotTable = $this->getProperty($reflection, $relation, 'table');
        $foreignPivotKey = $this->getProperty($reflection, $relation, 'foreignPivotKey') ?: 'post_id';
        $relatedPivotKey = $this->getProperty($reflection, $relation, 'relatedPivotKey') ?: 'rel_id';
        $parentKey = $this->getProperty($reflection, $relation, 'parentKey') ?: 'id';
        $relatedKey = $this->getProperty($reflection, $relation, 'relatedKey') ?: 'id_main';
        
        // Extract parent key values from results
        $parentValues = array_unique(array_filter(array_column($results, $parentKey)));
        
        if (empty($parentValues)) {
            foreach ($results as &$result) {
                $result[$relationship] = [];
            }
            return;
        }
        
        // Query related models with JOIN to pivot
        $query = $relation->getQuery();
        $relatedTable = $query->getFrom();
        
        // Apply constraints if provided
        if ($constraints) {
            $constraints($query);
        }
        
        // Join pivot table and filter by parent values
        // Get current select columns from query (if any), otherwise select all from related table
        $currentColumns = $query->getColumns();
        $hasCustomSelect = !($currentColumns === ['*'] || (count($currentColumns) === 1 && $currentColumns[0] === '*'));
        
        if (!$hasCustomSelect) {
            // No custom select - need to add all columns from related table + pivot field
            // Use raw SQL to avoid quoting issues with table.*
            $query->selectRaw("{$relatedTable}.*, {$pivotTable}.{$foreignPivotKey} as _pivot_parent_id");
        } else {
            // Has custom select - just add pivot field
            $currentColumns[] = "{$pivotTable}.{$foreignPivotKey} as _pivot_parent_id";
            $query->select($currentColumns);
        }
        
        $relatedModels = $query
            ->join($pivotTable, "{$relatedTable}.{$relatedKey}", '=', "{$pivotTable}.{$relatedPivotKey}")
            ->whereIn("{$pivotTable}.{$foreignPivotKey}", $parentValues)
            ->get();
        
        // Group by parent ID
        $grouped = [];
        foreach ($relatedModels as $model) {
            $parentId = $model['_pivot_parent_id'] ?? null;
            if ($parentId !== null) {
                if (!isset($grouped[$parentId])) {
                    $grouped[$parentId] = [];
                }
                unset($model['_pivot_parent_id']); // Remove pivot field from result
                $grouped[$parentId][] = $model;
            }
        }
        
        // Attach to results
        foreach ($results as &$result) {
            $parentValue = $result[$parentKey] ?? null;
            $result[$relationship] = $grouped[$parentValue] ?? [];
        }
    }
    
    /**
     * Get property value using reflection
     * 
     * @param \ReflectionClass $reflection
     * @param mixed $object
     * @param string $propertyName
     * @return mixed
     */
    protected function getProperty($reflection, $object, $propertyName)
    {
        if ($reflection->hasProperty($propertyName)) {
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);
            return $property->getValue($object);
        }
        return null;
    }

    /**
     * Get relationship type from relation instance.
     * 
     * @param mixed $relation Relationship instance
     * @return string
     */
    protected function getRelationType($relation)
    {
        $className = get_class($relation);
        $shortName = substr($className, strrpos($className, '\\') + 1);
        
        switch ($shortName) {
            case 'HasOne':
            case 'MorphOne':
                return 'hasOne';
            case 'HasMany':
            case 'MorphMany':
                return 'hasMany';
            case 'BelongsToMany':
            case 'MorphToMany':
                return 'belongsToMany';  // ✅ Separate type for pivot relationships
            case 'BelongsTo':
            case 'MorphTo':
                return 'belongsTo';
            default:
                return 'hasMany';
        }
    }

    /**
     * Get foreign key for relationship.
     * 
     * For BelongsTo: this is the column on the PARENT model (e.g., posts.author)
     * For HasMany: this is the column on the RELATED model (e.g., comments.post_id)
     * 
     * @param mixed $relation Relationship instance
     * @param string $relationType Relationship type
     * @return string
     */
    protected function getRelationForeignKey($relation, $relationType)
    {
        $reflection = new \ReflectionClass($relation);
        
        // Try to get foreignKey property directly
        if ($reflection->hasProperty('foreignKey')) {
            $property = $reflection->getProperty('foreignKey');
            $property->setAccessible(true);
            $foreignKey = $property->getValue($relation);
            if ($foreignKey) {
                return $foreignKey;
            }
        }
        
        // For BelongsTo, also try ownerKey property (this is what we query on related table)
        if ($relationType === 'belongsTo' && $reflection->hasProperty('ownerKey')) {
            $property = $reflection->getProperty('ownerKey');
            $property->setAccessible(true);
            $ownerKey = $property->getValue($relation);
            if ($ownerKey) {
                return $ownerKey;  // Return owner key as "foreign key" for eager loading query
            }
        }
        
        // Fallback defaults based on relationship type
        switch ($relationType) {
            case 'hasOne':
            case 'hasMany':
                return 'user_id';
            case 'belongsTo':
                return 'id';  // Default owner key for eager loading
            case 'belongsToMany':
                return 'id';
            default:
                return 'id';
        }
    }

    /**
     * Get local key for relationship.
     * 
     * @param mixed $relation Relationship instance
     * @param string $relationType Relationship type
     * @return string
     */
    protected function getRelationLocalKey($relation, $relationType)
    {
        $reflection = new \ReflectionClass($relation);
        
        // Try to get localKey property directly
        if ($reflection->hasProperty('localKey')) {
            $property = $reflection->getProperty('localKey');
            $property->setAccessible(true);
            $localKey = $property->getValue($relation);
            if ($localKey) {
                return $localKey;
            }
        }
        
        // Fallback defaults based on relationship type
        switch ($relationType) {
            case 'hasOne':
            case 'hasMany':
                // For hasOne/hasMany, local key is the primary key of current model
                return 'id';
            case 'belongsTo':
                // For belongsTo, local key is the foreign key on current model
                return 'user_id';
            case 'belongsToMany':
                // For belongsToMany, local key is the primary key of current model
                return 'id';
            default:
                return 'id';
        }
    }

    /**
     * Extract local key values from results.
     * 
     * @param array $results Query results
     * @param string $localKey Local key name
     * @return array
     */
    protected function extractLocalValues(array $results, $localKey)
    {
        $values = [];
        
        foreach ($results as $result) {
            if (isset($result[$localKey])) {
                $values[] = $result[$localKey];
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
     * @param array $results Parent models
     * @param array $groupedRelated Grouped related models
     * @param string $relationship Relationship name
     * @param string $localKey Local key name
     * @param string $foreignKey Foreign key name
     * @param string $relationType Relationship type
     */
    protected function attachRelatedModels(array &$results, array $groupedRelated, $relationship, $localKey, $foreignKey, $relationType)
    {
        foreach ($results as &$result) {
            $localValue = $result[$localKey] ?? null;
            
            if ($localValue !== null && isset($groupedRelated[$localValue])) {
                $related = $groupedRelated[$localValue];
                
                // For hasOne relationships, take first item
                // For hasMany relationships, take all items
                $result[$relationship] = ($relationType === 'hasOne' || $relationType === 'belongsTo') 
                    ? $related[0] 
                    : $related;
            } else {
                $result[$relationship] = ($relationType === 'hasOne' || $relationType === 'belongsTo') 
                    ? null 
                    : [];
            }
        }
    }


    /**
     * Get eager loader.
     * 
     * @return EagerLoader|null
     */
    public function getEagerLoader()
    {
        return $this->eagerLoader;
    }

    /**
     * Clear eager loading constraints.
     * 
     * @return $this
     */
    public function withoutEagerLoading()
    {
        $this->eagerLoader = null;
        return $this;
    }
}
