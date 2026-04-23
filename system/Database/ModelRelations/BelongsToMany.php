<?php
namespace System\Database\ModelRelations;

/**
 * BelongsToMany relationship
 */
class BelongsToMany extends Relation
{
    /** @var string */
    protected $table;
    
    /** @var string */
    protected $foreignPivotKey;
    
    /** @var string */
    protected $relatedPivotKey;
    
    /** @var string */
    protected $parentKey;
    
    /** @var string */
    protected $relatedKey;

    public function __construct($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey)
    {
        $this->table = $table;
        $this->foreignPivotKey = $foreignPivotKey;
        $this->relatedPivotKey = $relatedPivotKey;
        $this->parentKey = $parentKey;
        $this->relatedKey = $relatedKey;
        
        parent::__construct($query, $parent, $foreignPivotKey, $parentKey);
    }

    /**
     * Add constraints to the query.
     * 
     * @return void
     */
    public function addConstraints()
    {
        if (static::constraints()) {
            $this->query->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName())
                       ->where($this->getQualifiedForeignPivotKeyName(), '=', $this->parent->getAttribute($this->parentKey));
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
     * Attach a model to the relationship.
     * 
     * @param mixed $id
     * @param array $attributes
     * @return void
     */
    public function attach($id, array $attributes = [])
    {
        $id = is_object($id) ? $id->getAttribute($this->relatedKey) : $id;
        
        $attributes = array_merge($attributes, [
            $this->foreignPivotKey => $this->parent->getAttribute($this->parentKey),
            $this->relatedPivotKey => $id,
        ]);
        
        \System\Database\DB::table($this->table)->insert($attributes);
    }

    /**
     * Detach a model from the relationship.
     * 
     * @param mixed $ids
     * @return int
     */
    public function detach($ids = null)
    {
        $query = \System\Database\DB::table($this->table)
            ->where($this->foreignPivotKey, $this->parent->getAttribute($this->parentKey));
        
        if ($ids !== null) {
            $ids = is_array($ids) ? $ids : [$ids];
            $query->whereIn($this->relatedPivotKey, $ids);
        }
        
        return $query->delete();
    }

    /**
     * Sync the relationship.
     * 
     * @param array $ids
     * @param bool $detaching
     * @return array
     */
    public function sync(array $ids, $detaching = true)
    {
        $changes = [
            'attached' => [],
            'detached' => [],
            'updated' => [],
        ];
        
        $current = $this->newPivotQuery()->pluck($this->relatedPivotKey);
        
        $detach = array_diff($current, $ids);
        
        if ($detaching && count($detach) > 0) {
            $this->detach($detach);
            $changes['detached'] = $detach;
        }
        
        $attach = array_diff($ids, $current);
        
        if (count($attach) > 0) {
            $this->attach($attach);
            $changes['attached'] = $attach;
        }
        
        return $changes;
    }

    /**
     * Get a new pivot query.
     * 
     * @return \System\Database\Query\Builder
     */
    public function newPivotQuery()
    {
        return \System\Database\DB::table($this->table)
            ->where($this->foreignPivotKey, $this->parent->getAttribute($this->parentKey));
    }

    /**
     * Get the qualified foreign pivot key name.
     * 
     * @return string
     */
    public function getQualifiedForeignPivotKeyName()
    {
        return $this->table . '.' . $this->foreignPivotKey;
    }

    /**
     * Get the qualified related pivot key name.
     * 
     * @return string
     */
    public function getQualifiedRelatedPivotKeyName()
    {
        return $this->table . '.' . $this->relatedPivotKey;
    }

    /**
     * Get the qualified related key name.
     * 
     * @return string
     */
    public function getQualifiedRelatedKeyName()
    {
        return $this->query->getModel()->getTable() . '.' . $this->relatedKey;
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
