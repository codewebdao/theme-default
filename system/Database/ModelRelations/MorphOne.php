<?php
namespace System\Database\ModelRelations;

/**
 * MorphOne relationship
 */
class MorphOne extends Relation
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
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->first();
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
     * Update the related model.
     * 
     * @param array $attributes
     * @return int
     */
    public function update(array $attributes)
    {
        return $this->query->update($attributes);
    }

    /**
     * Delete the related model.
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
