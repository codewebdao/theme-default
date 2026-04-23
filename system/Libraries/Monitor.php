<?php
namespace System\Libraries;

class Monitor {

    // Initial time and memory
    private static $startTime = null;
    private static $startMemory = null;
    
    /**
     * Initialize start time and memory if not already set
     */
    private static function initStartTime() {
        if (self::$startTime === null) {
            self::$startTime = defined('APP_START_TIME') ? APP_START_TIME : microtime(true);
            self::$startMemory = defined('APP_START_MEMORY') ? APP_START_MEMORY : memory_get_usage();
        }
    }

    // Profiling markers
    protected static $markers = [];
    
    // Active markers stack (for tracking hierarchy)
    protected static $activeStack = [];
    
    // Children tracking (for calculating exclusive time)
    protected static $children = [];

    /**
     * Measure end of entire request from framework startup
     *
     * @return array Result with execution time, memory used, CPU load
     */
    public static function endFramework() {
        self::initStartTime();
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage();
        
        // ✅ FIX: Auto-complete any markers that weren't stopped
        // This handles cases where controller exits early (exit, die, redirect, etc.)
        // Process in reverse order of activeStack to maintain hierarchy
        $activeMarkers = array_reverse(self::$activeStack);
        foreach ($activeMarkers as $label) {
            if (isset(self::$markers[$label]) && isset(self::$markers[$label]['start']) && !isset(self::$markers[$label]['end'])) {
                self::$markers[$label]['end'] = $endTime;
                self::$markers[$label]['memory_end'] = $endMemory;
            }
        }
        
        // Also check all markers (in case some weren't in activeStack)
        foreach (self::$markers as $label => &$marker) {
            if (isset($marker['start']) && !isset($marker['end'])) {
                $marker['end'] = $endTime;
                $marker['memory_end'] = $endMemory;
            }
        }
        unset($marker); // Break reference
        
        // Cleanup active stack (all markers should be completed now)
        self::$activeStack = [];

        $executionTime = $endTime - self::$startTime;
        $memoryUsed = $endMemory - self::$startMemory;

        $cpuUsage = function_exists('sys_getloadavg') ? sys_getloadavg()[0] : 'Cannot get CPU load!';

        return [
            'execution_time' => $executionTime,
            'memory_used'    => self::formatMemorySize($memoryUsed),
            'cpu_usage'      => $cpuUsage
        ];
    }

    /**
     * Format memory size
     *
     * @param int $size Size
     * @return string Formatted size
     */
    public static function formatMemorySize($size) {
        if ($size < 1024) {
            return $size . ' Bytes';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } elseif ($size < 1073741824) {
            return round($size / 1048576, 2) . ' MB';
        } else {
            return round($size / 1073741824, 2) . ' GB';
        }
    }

    /**
     * (Alias) Format for profiling: use formatMemorySize()
     */
    protected static function formatMemory($bytes) {
        return self::formatMemorySize($bytes);
    }

    /**
     * Add item from buffer (used before Monitor class is autoloaded)
     * 
     * This method adds a single marker item from buffer into Monitor class.
     * Automatically handles parent-child relationship if parent is specified.
     * 
     * @param array $item Marker item with keys: label, start, end, memory_start, memory_end, parent, level, children
     * @return void
     */
    public static function addItem($item) {
        if (!empty($item) && !empty($item['label'])) {
            self::$markers[$item['label']] = [
                'start' => $item['start'] ?? null,
                'end' => $item['end'] ?? null,
                'memory_start' => $item['memory_start'] ?? null,
                'memory_end' => $item['memory_end'] ?? null,
                'parent' => $item['parent'] ?? null,
                'level' => $item['level'] ?? 0,
                'children' => $item['children'] ?? []
            ];
        }
    }

    /**
     * Mark profiling start point
     *
     * @param string $label Marker label
     */
    public static function mark($label) {
        // Track parent (current active marker on stack)
        $parent = !empty(self::$activeStack) ? end(self::$activeStack) : null;
        
        // Initialize marker
        self::$markers[$label] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(),
            'parent' => $parent,
            'level' => $parent ? (self::$markers[$parent]['level'] ?? 0) + 1 : 0,
            'children' => []
        ];
        
        // Add to parent's children list
        if ($parent) {
            if (!isset(self::$markers[$parent]['children'])) {
                self::$markers[$parent]['children'] = [];
            }
            self::$markers[$parent]['children'][] = $label;
        }
        
        // Push to active stack
        self::$activeStack[] = $label;
    }

    /**
     * Stop marked marker and calculate time, memory used
     * 
     * ✅ FIX: Auto-recover from mismatched mark/stop pairs
     *
     * @param string $label Marker label
     */
    public static function stop($label) {
        if (!isset(self::$markers[$label]['start'])) {
            return;
        }
        
        // Check if marker is in active stack
        $key = array_search($label, self::$activeStack);
        
        if ($key !== false) {
            // Normal case: marker is in active stack, remove it
            array_splice(self::$activeStack, $key, 1);
        } else {
            // Try to find and remove any markers that should have been stopped
            // Remove all markers from activeStack that have already ended
            self::$activeStack = array_filter(self::$activeStack, function($stackLabel) {
                // Keep only markers that haven't been stopped yet
                return !isset(self::$markers[$stackLabel]['end']);
            });
            
            // Re-index array after filter
            self::$activeStack = array_values(self::$activeStack);
            
            if (defined('APP_DEBUGBAR') && APP_DEBUGBAR) {
                error_log("Monitor::stop() called for marker '{$label}' that wasn't in active stack. Active stack cleaned up.");
            }
        }
        
        // Set end time and memory
        self::$markers[$label]['end'] = microtime(true);
        self::$markers[$label]['memory_end'] = memory_get_usage();
    }

    /**
     * Check if a child is actually nested within parent's time range
     * 
     * Used to filter out siblings that were marked as children but aren't actually nested
     *
     * @param string $parentLabel Parent marker label
     * @param string $childLabel Child marker label
     * @return bool True if child is nested within parent
     */
    private static function isChildNested($parentLabel, $childLabel) {
        if (!isset(self::$markers[$parentLabel]) || !isset(self::$markers[$childLabel])) {
            return false;
        }
        
        $parentMark = self::$markers[$parentLabel];
        $childMark = self::$markers[$childLabel];
        
        $parentStart = $parentMark['start'] ?? null;
        $parentEnd = $parentMark['end'] ?? microtime(true);
        $childStart = $childMark['start'] ?? null;
        $childEnd = $childMark['end'] ?? microtime(true);
        
        if ($parentStart === null || $childStart === null) {
            return false;
        }
        
        // ✅ FIX: Child must start after parent starts
        // For end time: allow child to end at same time as parent (handles auto-completed markers)
        // This is important when both parent and child are auto-completed in endFramework()
        $childStartsAfterParent = $childStart >= $parentStart;
        // Allow child to end at same time or before parent (with 1ms tolerance for floating point precision)
        $childEndsBeforeOrAtParentEnd = $childEnd <= $parentEnd || abs($childEnd - $parentEnd) < 0.001;
        
        return $childStartsAfterParent && $childEndsBeforeOrAtParentEnd;
    }

    /**
     * Build hierarchy tree from markers
     *
     * @return array Tree structure with children
     */
    private static function buildTree() {
        $tree = [];
        $allLabels = array_keys(self::$markers);
        
        // Find root nodes (no parent)
        foreach ($allLabels as $label) {
            $mark = self::$markers[$label];
            if (empty($mark['parent'])) {
                $tree[] = self::buildTreeNode($label);
            }
        }
        
        // Sort by start time
        usort($tree, function($a, $b) {
            $aStart = self::$markers[$a['label']]['start'] ?? 0;
            $bStart = self::$markers[$b['label']]['start'] ?? 0;
            return $aStart <=> $bStart;
        });
        
        return $tree;
    }

    /**
     * Build tree node recursively
     * 
     * ✅ FIX: Only include children that are actually nested within parent's time range
     *
     * @param string $label Marker label
     * @return array Node with children
     */
    private static function buildTreeNode($label) {
        $mark = self::$markers[$label];
        $node = [
            'label' => $label,
            'level' => $mark['level'] ?? 0,
            'children' => []
        ];
        
        // Add children (only if they are actually nested)
        if (!empty($mark['children'])) {
            foreach ($mark['children'] as $childLabel) {
                // ✅ FIX: Only add if child is actually nested within parent
                if (self::isChildNested($label, $childLabel)) {
                    $node['children'][] = self::buildTreeNode($childLabel);
                }
            }
            
            // Sort children by start time
            usort($node['children'], function($a, $b) {
                $aStart = self::$markers[$a['label']]['start'] ?? 0;
                $bStart = self::$markers[$b['label']]['start'] ?? 0;
                return $aStart <=> $bStart;
            });
        }
        
        return $node;
    }

    /**
     * Flatten tree to list with hierarchy info
     * 
     * ✅ FIX: Each marker shows its own time (from mark to stop), not sum of children
     *
     * @param array $tree Tree structure
     * @return array Flattened list
     */
    private static function flattenTree($tree) {
        $results = [];
        
        foreach ($tree as $node) {
            $label = $node['label'];
            $mark = self::$markers[$label];
            
            $startTime = $mark['start'] ?? null;
            if ($startTime === null) {
                continue;
            }
            
            $endTime = $mark['end'] ?? microtime(true);
            // ✅ FIX: Time is simply from mark to stop (not sum of children)
            $timeMs = max(($endTime - $startTime) * 1000, 0);
            $hasChildren = !empty($node['children']);
            
            $memoryStart = $mark['memory_start'] ?? null;
            $memoryEnd = $mark['memory_end'] ?? memory_get_usage();
            $memoryUsed = ($memoryStart !== null) ? max($memoryEnd - $memoryStart, 0) : 0;
            
            $results[] = [
                'label' => $label,
                'level' => $node['level'],
                'time_ms' => $timeMs, // Time from mark to stop (same for parent and child)
                'has_children' => $hasChildren,
                'memory_bytes' => $memoryUsed,
                'children' => self::flattenTree($node['children'])
            ];
        }
        
        return $results;
    }

    /**
     * Get list of marked profiles with hierarchy
     *
     * @return array Profile array with label, time, memory usage, and hierarchy
     */
    public static function getProfiles() {
        $tree = self::buildTree();
        return self::flattenTree($tree);
    }
}
