<?php
namespace System\Database\ModelRelations;

/**
 * MorphTo relationship
 */
class MorphTo extends Relation
{
    /** @var string */
    protected $type;
    
    /** @var string */
    protected $id;
    
    /** @var string */
    protected $ownerKey;
    
    /** @var string */
    protected $name;

    public function __construct($query, $parent, $type, $id, $ownerKey, $name)
    {
        $this->type = $type;
        $this->id = $id;
        $this->ownerKey = $ownerKey;
        $this->name = $name;
        
        parent::__construct($query, $parent, $id, $id);
    }

    /**
     * Add constraints to the query.
     * 
     * @return void
     */
    public function addConstraints()
    {
        if (static::constraints()) {
            $this->query->where($this->ownerKey, '=', $this->parent->getAttribute($this->foreignKey));
        }
    }

    /**
     * Get the results of the relationship.
     * 
     * @return mixed
     */
    public function getResults()
    {
        $type = $this->parent->getAttribute($this->type);
        
        if ($type === null) {
            return null;
        }
        
        $class = $this->getMorphClass($type);
        $instance = new $class;
        
        return $instance->newQuery()->where($instance->getKeyName(), $this->parent->getAttribute($this->foreignKey))->first();
    }

    /**
     * Get the morph class for the given type.
     * 
     * @param string $type
     * @return string
     */
    protected function getMorphClass($type)
    {
        // Simple implementation - in real Laravel this would use a morph map
        return $type;
    }

    /**
     * Associate a model with this relationship.
     * 
     * @param mixed $model
     * @return void
     */
    public function associate($model)
    {
        $ownerKey = is_object($model) ? $model->getAttribute($this->ownerKey) : $model;
        $type = is_object($model) ? $model->getMorphClass() : get_class($model);
        
        $this->parent->setAttribute($this->foreignKey, $ownerKey);
        $this->parent->setAttribute($this->type, $type);
    }

    /**
     * Dissociate the relationship.
     * 
     * @return void
     */
    public function dissociate()
    {
        $this->parent->setAttribute($this->foreignKey, null);
        $this->parent->setAttribute($this->type, null);
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
     * Get the morph name.
     * 
     * @return string
     */
    public function getMorphName()
    {
        return $this->name;
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
