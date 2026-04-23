<?php
namespace System\Database\ModelRelations;

use System\Database\Query\Builder;

/**
 * Base Relation class for Eloquent-style relationships
 */
abstract class Relation
{
    /** @var Builder */
    protected $query;
    
    /** @var \System\Database\BaseModel */
    protected $parent;
    
    /** @var string */
    protected $foreignKey;
    
    /** @var string */
    protected $localKey;

    public function __construct(Builder $query, $parent, $foreignKey, $localKey)
    {
        $this->query = $query;
        $this->parent = $parent;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    /**
     * Get the query builder for this relationship.
     * 
     * @return Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the parent model.
     * 
     * @return \System\Database\BaseModel
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the foreign key.
     * 
     * @return string
     */
    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * Get the local key.
     * 
     * @return string
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

    /**
     * Add constraints to the query.
     * 
     * @return void
     */
    abstract public function addConstraints();

    /**
     * Get the results of the relationship.
     * 
     * @return mixed
     */
    abstract public function getResults();

    /**
     * Execute the query and get the first result.
     * 
     * @return mixed
     */
    public function first()
    {
        return $this->getQuery()->first();
    }

    /**
     * Execute the query and get all results.
     * 
     * @return array
     */
    public function get()
    {
        return $this->getQuery()->get();
    }

    /**
     * Count the number of results.
     * 
     * @return int
     */
    public function count()
    {
        return $this->getQuery()->count();
    }

    /**
     * Check if the relationship has any results.
     * 
     * @return bool
     */
    public function exists()
    {
        return $this->getQuery()->exists();
    }

    /**
     * Add a where clause to the relationship query.
     * 
     * @param string $column
     * @param mixed $operator
     * @param mixed $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null)
    {
        $this->query->where($column, $operator, $value);
        return $this;
    }

    /**
     * Add an order by clause to the relationship query.
     * 
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }

    /**
     * Add a limit to the relationship query.
     * 
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->query->limit($limit);
        return $this;
    }
}
