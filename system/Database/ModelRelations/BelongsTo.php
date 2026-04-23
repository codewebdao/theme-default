<?php
namespace System\Database\ModelRelations;

/**
 * BelongsTo relationship
 */
class BelongsTo extends Relation
{
    /** @var string */
    protected $ownerKey;

    public function __construct($query, $parent, $foreignKey, $ownerKey)
    {
        $this->ownerKey = $ownerKey;
        parent::__construct($query, $parent, $foreignKey, $foreignKey);
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
        return $this->query->first();
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
        
        $this->parent->setAttribute($this->foreignKey, $ownerKey);
    }

    /**
     * Dissociate the relationship.
     * 
     * @return void
     */
    public function dissociate()
    {
        $this->parent->setAttribute($this->foreignKey, null);
    }

    /**
     * Get the owner key.
     * 
     * @return string
     */
    public function getOwnerKey()
    {
        return $this->ownerKey;
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
