<?php
namespace System\Database\ModelRelations;

/**
 * HasMany relationship
 */
class HasMany extends Relation
{
    /**
     * Add constraints to the query.
     * 
     * @return void
     */
    public function addConstraints()
    {
        if (static::constraints()) {
            $this->query->where($this->foreignKey, '=', $this->parent->getAttribute($this->localKey));
        }
    }

    /**
     * Get the results of the relationship.
     * 
     * @return array
     */
    public function getResults()
    {
        return $this->query->get();
    }

    /**
     * Create a new instance of the related model.
     * 
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes = [])
    {
        $attributes[$this->foreignKey] = $this->parent->getAttribute($this->localKey);
        
        $modelClass = $this->query->getModel();
        if ($modelClass) {
            return (new $modelClass)->create($attributes);
        }
        
        // Fallback to direct insert if no model class
        return $this->query->insertGetId($attributes);
    }

    /**
     * Create many instances of the related model.
     * 
     * @param array $records
     * @return int
     */
    public function createMany(array $records)
    {
        foreach ($records as &$record) {
            $record[$this->foreignKey] = $this->parent->getAttribute($this->localKey);
        }
        
        $modelClass = $this->query->getModel();
        if ($modelClass) {
            return (new $modelClass)->createMany($records);
        }
        
        // Fallback to direct insert if no model class
        return $this->query->insertMany($records);
    }

    /**
     * Update the related models.
     * 
     * @param array $attributes
     * @return int
     */
    public function update(array $attributes)
    {
        return $this->query->update($attributes);
    }

    /**
     * Delete the related models.
     * 
     * @return int
     */
    public function delete()
    {
        return $this->query->delete();
    }

    /**
     * Check if constraints should be applied.
     * 
     * @return bool
     */
    protected static function constraints()
    {
        return true;
    }
}
