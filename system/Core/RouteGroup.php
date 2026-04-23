<?php

namespace System\Core;

/**
 * RouteGroup - Context for route groups
 * 
 * Stores group attributes (prefix, middleware, namespace, etc.)
 */
class RouteGroup
{
    /** @var string|null URI prefix */
    public $prefix = null;
    
    /** @var array Middleware stack */
    public $middleware = [];
    
    /** @var string|null Namespace */
    public $namespace = null;
    
    /** @var string|null Name prefix */
    public $namePrefix = null;
    
    /** @var array Parameter constraints */
    public $where = [];
    
    /** @var RouteGroup|null Parent group */
    public $parent = null;

    /**
     * Constructor
     */
    public function __construct($attributes = [])
    {
        $this->prefix = $attributes['prefix'] ?? null;
        $this->middleware = $attributes['middleware'] ?? [];
        $this->namespace = $attributes['namespace'] ?? null;
        $this->namePrefix = $attributes['namePrefix'] ?? null;
        $this->where = $attributes['where'] ?? [];
        $this->parent = $attributes['parent'] ?? null;
    }

    /**
     * Merge with parent group
     */
    public function mergeWithParent()
    {
        if ($this->parent) {
            // Merge prefix
            if ($this->parent->prefix) {
                $this->prefix = rtrim($this->parent->prefix, '/') . '/' . ltrim($this->prefix ?? '', '/');
            }
            
            // Merge middleware
            $this->middleware = array_merge($this->parent->middleware, $this->middleware);
            
            // Merge namespace (child overrides parent)
            if (!$this->namespace && $this->parent->namespace) {
                $this->namespace = $this->parent->namespace;
            }
            
            // Merge name prefix
            if ($this->parent->namePrefix) {
                $this->namePrefix = $this->parent->namePrefix . ($this->namePrefix ?? '');
            }
            
            // Merge where constraints
            $this->where = array_merge($this->parent->where, $this->where);
        }
    }
}

