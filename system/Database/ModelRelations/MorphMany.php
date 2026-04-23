<?php
namespace System\Database\ModelRelations;

/**
 * MorphMany relationship
 */
class MorphMany extends Relation
{
    /** @var string */
    protected $type;
    
    /** @var string */
    protected $id;

    public function __construct($query, $parent, $type, $id, $localKey)
    {
        $this->type = $type;
        $this->id = $id;
        
        parent::__construct($query, $parent, $id, $localKey);
    }

    /**
     * Add constraints to the query.
     * 
     * @return void
     */
    public function addConstraints()
    {
        if (static::constraints()) {
            $this->query->where($this->foreignKey, '=', $this->parent->getAttribute($this->localKey))
                       ->where($this->type, '=', $this->parent->getMorphClass());
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
        $attributes[$this->type] = $this->parent->getMorphClass();
        
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
            $record[$this->type] = $this->parent->getMorphClass();
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
     * Get the morph type.
     * 
     * @return string
     */
    public function getMorphType()
    {
        return $this->type;
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
