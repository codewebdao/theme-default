<?php
namespace System\Database\Query;

/**
 * JoinClause class để xử lý join conditions
 * Tương tự Laravel's JoinClause
 */
class JoinClause
{
    public $left = '';
    public $op = '';
    public $right = '';
    
    private $grammar;
    private $alias;
    
    public function __construct($grammar, $alias)
    {
        $this->grammar = $grammar;
        $this->alias = $alias;
    }
    
    /**
     * Add join condition: ON left op right
     * @param string $left
     * @param string $op
     * @param string $right
     * @return $this
     */
    public function on($left, $op, $right)
    {
        $this->left = $left;
        $this->op = $op;
        $this->right = $right;
        return $this;
    }
    
    /**
     * Add join condition with AND
     * @param string $left
     * @param string $op
     * @param string $right
     * @return $this
     */
    public function andOn($left, $op, $right)
    {
        // For now, just use on() - can be extended later for multiple conditions
        return $this->on($left, $op, $right);
    }
    
    /**
     * Add join condition with OR
     * @param string $left
     * @param string $op
     * @param string $right
     * @return $this
     */
    public function orOn($left, $op, $right)
    {
        // For now, just use on() - can be extended later for multiple conditions
        return $this->on($left, $op, $right);
    }
}
