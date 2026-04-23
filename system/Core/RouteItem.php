<?php

namespace System\Core;

/**
 * RouteItem - Encapsulate route data
 * 
 * Represents a single route with all its properties
 */
class RouteItem
{
    /** @var string HTTP method */
    public $method;
    
    /** @var string Route URI pattern */
    public $uri;
    
    /** @var string|callable Controller or closure */
    public $action;
    
    /** @var array Middleware stack */
    public $middleware = [];
    
    /** @var string|null Route name */
    public $name = null;
    
    /** @var string|null Namespace */
    public $namespace = null;
    
    /** @var array Parameter constraints */
    public $where = [];
    
    /** @var array Route parameters (for model binding) */
    public $parameters = [];
    
    /** @var int Priority (higher = matched first) */
    public $priority = 0;
    
    /** @var string Compiled regex pattern */
    public $compiled = null;
    
    /** @var array Parameter names extracted from URI */
    public $parameterNames = [];

    /**
     * Constructor
     */
    public function __construct($method, $uri, $action, $options = [])
    {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->action = $action;
        
        // Extract options
        $this->middleware = $options['middleware'] ?? [];
        $this->name = $options['name'] ?? null;
        $this->namespace = $options['namespace'] ?? null;
        $this->where = $options['where'] ?? [];
        $this->parameters = $options['parameters'] ?? [];
        $this->priority = $options['priority'] ?? 0;
        
        // Extract parameter names from URI
        $this->extractParameterNames();
    }

    /**
     * Extract parameter names from URI
     */
    protected function extractParameterNames()
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)(\?)?\}/', $this->uri, $matches);
        if (!empty($matches[1])) {
            $this->parameterNames = $matches[1];
        }
    }

    /**
     * Set route name
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Add middleware
     */
    public function middleware($middleware)
    {
        if (is_array($middleware)) {
            $this->middleware = array_merge($this->middleware, $middleware);
        } else {
            $this->middleware[] = $middleware;
        }
        return $this;
    }

    /**
     * Set parameter constraints
     */
    public function where($name, $pattern = null)
    {
        if (is_array($name)) {
            $this->where = array_merge($this->where, $name);
        } else {
            $this->where[$name] = $pattern;
        }
        return $this;
    }

    /**
     * Set whereNumber constraint
     */
    public function whereNumber($name)
    {
        $this->where[$name] = '[0-9]+';
        return $this;
    }

    /**
     * Set whereAlpha constraint
     */
    public function whereAlpha($name)
    {
        $this->where[$name] = '[a-zA-Z]+';
        return $this;
    }

    /**
     * Set whereAlphaNumeric constraint
     */
    public function whereAlphaNumeric($name)
    {
        $this->where[$name] = '[a-zA-Z0-9]+';
        return $this;
    }

    /**
     * Set whereUuid constraint
     */
    public function whereUuid($name)
    {
        $this->where[$name] = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}';
        return $this;
    }

    /**
     * Set whereIn constraint
     */
    public function whereIn($name, $values)
    {
        $escaped = array_map(function($v) {
            return preg_quote($v, '/');
        }, $values);
        $this->where[$name] = '(' . implode('|', $escaped) . ')';
        return $this;
    }

    /**
     * Compile route to regex pattern
     */
    public function compile()
    {
        if ($this->compiled !== null) {
            return $this->compiled;
        }

        $pattern = $this->uri;
        
        // First, escape all special regex characters
        $pattern = preg_quote($pattern, '#');
        
        // Then replace \{param\} placeholders with regex groups
        $pattern = preg_replace_callback('/\\\{([a-zA-Z0-9_]+)(\\\?)?\\\}/', function($matches) {
            $paramName = $matches[1];
            $isOptional = isset($matches[2]) && $matches[2] === '\\?';
            
            // Get constraint from where array or use default
            $constraint = $this->where[$paramName] ?? '[^/]+';
            
            if ($isOptional) {
                return '(' . $constraint . ')?';
            }
            
            return '(' . $constraint . ')';
        }, $pattern);
        
        $this->compiled = '#^' . $pattern . '$#';
        
        return $this->compiled;
    }

    /**
     * Match URI against this route
     */
    public function matches($uri)
    {
        $pattern = $this->compile();
        return preg_match($pattern, $uri, $matches) ? $matches : false;
    }

    /**
     * Extract parameters from matched URI
     */
    public function extractParameters($matches)
    {
        array_shift($matches); // Remove full match
        
        $params = [];
        foreach ($this->parameterNames as $index => $name) {
            if (isset($matches[$index]) && $matches[$index] !== '') {
                $params[$name] = $matches[$index];
            } elseif (!isset($this->where[$name])) {
                // Optional parameter without value
                $params[$name] = null;
            }
        }
        
        return $params;
    }

    /**
     * Get controller class and action
     */
    public function getControllerAction($params = [])
    {
        // If action is closure, return as-is
        if (is_callable($this->action) && !is_string($this->action)) {
            return [
                'type' => 'closure',
                'action' => $this->action,
                'params' => array_values($params)
            ];
        }
        
        // Parse controller string
        $action = $this->action;
        
        // Replace $n placeholders with params (backward compatibility)
        if (is_string($action)) {
            preg_match_all('/\$(\d+)/', $action, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $key => $paramIndex) {
                    $index = intval($paramIndex) - 1;
                    if (isset($params[$index])) {
                        $value = $params[$index];
                        if (strpos($value, '/') !== false) {
                            $value = explode('/', $value)[0];
                        }
                        if ($key < 2) {
                            $value = str_replace('.', '', $value);
                        }
                        if (strpos($action, '::') > strpos($action, '$' . $paramIndex)) {
                            $value = ucfirst($value);
                        }
                        $action = str_replace('$' . $paramIndex, $value, $action);
                    }
                }
            }
            
            // Parse controller::action
            if (strpos($action, '::') !== false) {
                list($controller, $actionString) = explode('::', $action, 2);
                
                // Split action and parameters
                $actionParts = explode(':', $actionString);
                $method = array_shift($actionParts);
                
                // Build controller class
                $namespace = $this->namespace ?? 'App\\Controllers';
                $controllerClass = $namespace . '\\' . $controller;
                
                return [
                    'type' => 'controller',
                    'controller' => $controllerClass,
                    'action' => $method,
                    'params' => array_merge($actionParts, array_values($params))
                ];
            }
        }
        
        // Fallback: assume it's a controller class
        return [
            'type' => 'controller',
            'controller' => is_string($action) ? $action : get_class($action),
            'action' => 'index',
            'params' => array_values($params)
        ];
    }

    /**
     * Apply group context
     */
    public function applyGroupContext(RouteGroup $group)
    {
        // Apply prefix
        if ($group->prefix) {
            $this->uri = rtrim($group->prefix, '/') . '/' . ltrim($this->uri, '/');
        }
        
        // Apply namespace
        if ($group->namespace && !$this->namespace) {
            $this->namespace = $group->namespace;
        }
        
        // Merge middleware
        if (!empty($group->middleware)) {
            $this->middleware = array_merge($group->middleware, $this->middleware);
        }
        
        // Apply name prefix
        if ($this->name && $group->namePrefix) {
            $this->name = $group->namePrefix . $this->name;
        }
        
        // Merge where constraints
        if (!empty($group->where)) {
            $this->where = array_merge($group->where, $this->where);
        }
    }
}

