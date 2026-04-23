<?php
/**
 * Debugbar UI — load qua View::renderDebugbarHtml() (parts/ui/debugbar.php).
 */
use System\Database\DB;
if (defined('APP_DEBUGBAR_SKIP') && APP_DEBUGBAR_SKIP) {
    return;
}
$debug_sql = DB::getQueryLog();


global $debug_cache;
// Get data for debugbar
$performance = \System\Libraries\Monitor::endFramework();
$profiles = \System\Libraries\Monitor::getProfiles();
$views = \System\Libraries\Render\View::getTrackedViews();
$logs = \System\Libraries\Logger::getLogs();
$hooks_data = \System\Libraries\Hooks::getDebugBarData();
$hooks_history = \System\Libraries\Hooks::getHistory();
$request = [
    'method'     => $_SERVER['REQUEST_METHOD'] ?? '',
    'uri'        => '/' . (APP_URI['uri'] ?? ''),
    'controller' => defined('APP_ROUTE') ? APP_ROUTE['controller'] : '404',
    'action'     => defined('APP_ROUTE') ? APP_ROUTE['action'] : '404',
    'headers'    => function_exists('getallheaders') ? getallheaders() : []
];
$environment = [
    'app_name'         => config('app_name') ?? 'CMSFullForm',
    'debug'            => defined('APP_DEBUG') && APP_DEBUG ? true : false,
    'php_version'      => phpversion(),
    'memory_limit'     => ini_get('memory_limit'),
    'loaded_extensions' => get_loaded_extensions(),
    '_server'   => $_SERVER
];

$db_css = function_exists('theme_assets') ? theme_assets('css/debugbar.css') : '';
$db_js = function_exists('theme_assets') ? theme_assets('js/debugbar.js') : '';
?>
<?php if ($db_css !== ''): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($db_css, ENT_QUOTES, 'UTF-8') ?>" id="debugbar-theme-css">
<?php endif; ?>
<!-- Debug Panel -->
<div id="debugbar" class="collapsed" data-active-tab="sql">
    
    <!-- Resize Handle -->
    <div class="debugbar-resize-handle" id="debugbar-resize-handle"></div>
    
    <!-- Header -->
    <div class="debugbar-header" id="debugbar-header">
        <div class="debugbar-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                <path d="M2 17l10 5 10-5"></path>
                <path d="M2 12l10 5 10-5"></path>
            </svg>
            <span>Debug Panel</span>
        </div>
        
        <div class="debugbar-stats">
            <div class="debugbar-stat-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span class="debugbar-stat-value"><?= round($performance['execution_time'] * 1000, 2) ?>ms</span>
            </div>
            <div class="debugbar-stat-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="10" rx="2" ry="2"></rect>
                </svg>
                <span class="debugbar-stat-value"><?= $performance['memory_used'] ?></span>
            </div>
            <?php if (isset($debug_sql) && count($debug_sql) > 0): ?>
            <div class="debugbar-stat-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
                <span class="debugbar-stat-value"><?= count($debug_sql) ?> queries</span>
            </div>
            <?php endif; ?>
            <?php if (isset($views) && count($views) > 0): ?>
            <div class="debugbar-stat-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <span class="debugbar-stat-value"><?= count($views) ?> views</span>
            </div>
            <?php endif; ?>
            <?php if (isset($hooks_data['summary']['total_hooks']) && $hooks_data['summary']['total_hooks'] > 0): ?>
            <div class="debugbar-stat-item">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M12 1v6m0 6v6"></path>
                    <path d="m4.93 4.93 4.24 4.24m5.66 5.66 4.24 4.24"></path>
                    <path d="m19.07 4.93-4.24 4.24m-5.66 5.66-4.24 4.24"></path>
                </svg>
                <span class="debugbar-stat-value"><?= $hooks_data['summary']['total_hooks'] ?> hooks</span>
            </div>
            <?php endif; ?>
        </div>

        <button class="debugbar-toggle-btn" type="button" id="debugbar-toggle-btn">
            <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
            <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>
    </div>

    <!-- Content -->
    <div class="debugbar-content">
        <!-- Tabs -->
        <div class="debugbar-tabs">
            <button class="debugbar-tab active" data-tab="sql">
                SQL (<?= isset($debug_sql) ? count($debug_sql) : 0 ?>)
            </button>
            <button class="debugbar-tab" data-tab="profiling">
                Profiling (<?= isset($profiles) ? count($profiles) : 0 ?>)
            </button>
            <button class="debugbar-tab" data-tab="views">
                Views (<?= isset($views) ? count($views) : 0 ?>)
            </button>
            <button class="debugbar-tab" data-tab="request">
                Request
            </button>
            <button class="debugbar-tab" data-tab="environment">
                Environment
            </button>
            <button class="debugbar-tab" data-tab="hooks">
                Hooks (<?= $hooks_data['summary']['total_hooks'] ?? 0 ?>)
            </button>
            <button class="debugbar-tab" data-tab="cache">
                Cache
            </button>
            <button class="debugbar-tab" data-tab="logs">
                Logs (<?= count($logs) ?>)
            </button>
        </div>

        <!-- Panels -->
        
        <div class="debugbar-panels-scroll">
            <!-- SQL Panel -->
            <div class="debugbar-panel active" data-panel="sql">
                <?php if (isset($debug_sql) && !empty($debug_sql)): ?>
                    <!-- SQL Summary -->
                    <div class="debugbar-section" data-section="sql-summary">
                        <div class="debugbar-section-title">
                            <span>📊 SQL Summary</span>
                            <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="debugbar-section-content">
                            <div class="debugbar-stats-grid">
                                <div class="debugbar-stat-card">
                                    <div class="debugbar-stat-card-label">Total Queries</div>
                                    <div class="debugbar-stat-card-value"><?= count($debug_sql) ?></div>
                                </div>
                                <div class="debugbar-stat-card">
                                    <div class="debugbar-stat-card-label">Total Time</div>
                                    <div class="debugbar-stat-card-value"><?= round(array_sum(array_column($debug_sql, 'time_ms')), 2) ?>ms</div>
                                </div>
                                <div class="debugbar-stat-card">
                                    <div class="debugbar-stat-card-label">Average Time</div>
                                    <div class="debugbar-stat-card-value"><?= round((array_sum(array_column($debug_sql, 'time_ms')) / count($debug_sql)), 2) ?>ms</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SQL Queries List -->
                    <div class="debugbar-section" data-section="sql-queries">
                        <div class="debugbar-section-title">
                            <span>🔍 SQL Queries (<?= count($debug_sql) ?>)</span>
                            <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="debugbar-section-content">
                            <?php foreach ($debug_sql as $i => $q): ?>
                                <div class="debugbar-query">
                                    <div class="debugbar-query-header">
                                        <div class="debugbar-query-info">
                                            <span class="debugbar-query-number">Query #<?= $i + 1 ?></span>
                                            
                                            <span class="debugbar-badge debugbar-badge-primary">
                                                <?= htmlspecialchars($q['connection']) ?>
                                            </span>
                                            
                                            <span class="debugbar-badge debugbar-badge-primary">
                                                <?= htmlspecialchars($q['node']) ?>
                                            </span>
                                            
                                            <span class="debugbar-badge <?= $q['intent'] === 'read' ? 'debugbar-badge-success' : 'debugbar-badge-warning' ?>">
                                                <?= strtoupper(htmlspecialchars($q['intent'])) ?>
                                            </span>
                                            
                                            <?php 
                                            $statusClass = 'debugbar-badge-success';
                                            $statusText = 'SUCCESS';
                                            if (isset($q['status'])) {
                                                if ($q['status'] === 'error') {
                                                    $statusClass = 'debugbar-badge-error';
                                                    $statusText = 'ERROR';
                                                } elseif ($q['status'] === 'pending') {
                                                    $statusClass = 'debugbar-badge-warning';
                                                    $statusText = 'PENDING';
                                                }
                                            }
                                            ?>
                                            <span class="debugbar-badge <?= $statusClass ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </div>
                                        
                                        <div class="debugbar-query-actions">
                                            <?php 
                                            $sqlFull = '';
                                            if (isset($q['sql_rendered']) && $q['sql_rendered'] !== $q['sql_raw']) {
                                                $sqlFull = $q['sql_rendered'];
                                            } elseif (isset($q['sql_raw'])) {
                                                $sqlFull = $q['sql_raw'];
                                            }
                                            ?>
                                            <?php if ($sqlFull): ?>
                                                <button class="debugbar-copy-btn" onclick="copyToClipboard('full-<?= $i ?>', event)">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                                    </svg>
                                                    Full
                                                </button>
                                                <textarea id="full-<?= $i ?>" style="position: absolute; left: -9999px;"><?= htmlspecialchars($sqlFull) ?></textarea>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($q['sql_raw'])): ?>
                                                <button class="debugbar-copy-btn" onclick="copyToClipboard('raw-<?= $i ?>', event)">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                                    </svg>
                                                    Raw
                                                </button>
                                                <textarea id="raw-<?= $i ?>" style="position: absolute; left: -9999px;"><?= htmlspecialchars($q['sql_raw']) ?></textarea>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($q['params'])): ?>
                                                <button class="debugbar-copy-btn" onclick="copyToClipboard('params-<?= $i ?>', event)">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                                    </svg>
                                                    Params
                                                </button>
                                                <textarea id="params-<?= $i ?>" style="position: absolute; left: -9999px;"><?= htmlspecialchars(json_encode($q['params'], JSON_PRETTY_PRINT)) ?></textarea>
                                            <?php endif; ?>
                                            
                                            <span class="debugbar-badge debugbar-badge-success">
                                                <?= $q['time_ms'] ?>ms
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <?php if (isset($q['error']) && $q['error']): ?>
                                        <div class="debugbar-error-box">
                                            <div class="debugbar-error-title">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="10"></circle>
                                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                                </svg>
                                                Error
                                            </div>
                                            <div class="debugbar-error-message"><?= htmlspecialchars($q['error']) ?></div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="debugbar-query-sql"><?php 
                                        if (isset($q['sql_rendered']) && $q['sql_rendered'] !== $q['sql_raw']) {
                                            echo htmlspecialchars(trim($q['sql_rendered']));
                                        } elseif (isset($q['sql_raw'])) {
                                            echo htmlspecialchars(trim($q['sql_raw']));
                                        }
                                        ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="debugbar-empty">
                        <div class="debugbar-empty-icon">📊</div>
                        <div class="debugbar-empty-title">No SQL queries executed</div>
                        <div class="debugbar-empty-desc">SQL queries will appear here when they are executed</div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Profiling Panel -->
            <div class="debugbar-panel" data-panel="profiling">
                <?php if (isset($profiles) && !empty($profiles)): ?>
                    <?php
                    // Count total profiles (including nested)
                    function countProfiles($profiles) {
                        $count = count($profiles);
                        foreach ($profiles as $p) {
                            if (!empty($p['children'])) {
                                $count += countProfiles($p['children']);
                            }
                        }
                        return $count;
                    }
                    $totalProfiles = countProfiles($profiles);
                    
                    // Calculate total time (only level 0 - root nodes)
                    $totalTime = 0;
                    foreach ($profiles as $p) {
                        if (($p['level'] ?? 0) === 0) {
                            $totalTime += $p['total_time_ms'] ?? $p['time_ms'] ?? 0;
                        }
                    }
                    ?>
                    
                    <!-- Profiling Details -->
                    <div class="debugbar-section" data-section="profiling-details">
                        <div class="debugbar-section-title">
                            <span>🔍 Profiling Details (<?= $totalProfiles ?> markers)</span>
                            <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="debugbar-section-content">
                            <?php
                            $profileIndex = 0;
                            function renderProfile($p, $level = 0, &$index = 0) {
                                global $profileIndex;
                                $currentIndex = $index++;
                                $uniqueId = 'profile-' . $currentIndex;
                                
                                $hasChildren = !empty($p['children']);
                                
                                $indent = $level * 1.5; // rem
                                $displayLabel = htmlspecialchars($p['label']);
                                $memory = \System\Libraries\Monitor::formatMemorySize($p['memory_bytes'] ?? 0);
                                
                                // ✅ FIX: Both parent and child show their own time (from mark to stop)
                                $timeMs = round($p['time_ms'] ?? 0, 2);
                                $timeDisplay = $timeMs . 'ms';
                                ?>
                                <div class="debugbar-list-item profile-item" data-profile-id="<?= $uniqueId ?>" style="padding-left: <?= $indent ?>rem;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1;">
                                        <?php if ($hasChildren): ?>
                                            <button class="profile-toggle-btn" onclick="toggleProfile('<?= $uniqueId ?>')" style="background: transparent; border: none; cursor: pointer; padding: 0.25rem; display: flex; align-items: center; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%)); transition: transform 0.2s;">
                                                <svg id="profile-icon-<?= $uniqueId ?>" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transform: rotate(-90deg); transition: transform 0.2s;">
                                                    <polyline points="9 18 15 12 9 6"></polyline>
                                                </svg>
                                            </button>
                                        <?php else: ?>
                                            <span style="width: 12px; display: inline-block;"></span>
                                        <?php endif; ?>
                                        <span class="debugbar-list-item-label" style="font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace; font-size: 0.8125rem;">
                                            <?= $displayLabel ?>
                                        </span>
                                    </div>
                                    <div style="display: flex; gap: 1rem; align-items: center;">
                                        <span class="debugbar-list-item-value" style="white-space: nowrap;"><?= $timeDisplay ?></span>
                                        <span class="debugbar-list-item-value" style="font-size: 0.75rem; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));"><?= $memory ?></span>
                                    </div>
                                </div>
                                <?php if ($hasChildren): ?>
                                    <div id="profile-children-<?= $uniqueId ?>" class="profile-children" style="display: none;">
                                        <?php
                                        foreach ($p['children'] as $child) {
                                            renderProfile($child, $level + 1, $index);
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                                <?php
                            }
                            
                            foreach ($profiles as $p) {
                                renderProfile($p, $p['level'] ?? 0, $profileIndex);
                            }
                            ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="debugbar-empty">
                        <div class="debugbar-empty-icon">⏱️</div>
                        <div class="debugbar-empty-title">No profiling data available</div>
                        <div class="debugbar-empty-desc">Profiling data will appear here when profiling is enabled</div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Views Panel -->
            <div class="debugbar-panel" data-panel="views">
                <?php if (!empty($views)): ?>
                    <!-- Views Summary -->
                    <div class="debugbar-section" data-section="views-summary">
                        <div class="debugbar-section-title">
                            <span>📊 Views Summary</span>
                            <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="debugbar-section-content">
                            <div class="debugbar-stats-grid">
                                <?php 
                                $viewTypes = array_count_values(array_column($views, 'type'));
                                $totalDuration = 0;
                                $viewsWithDuration = 0;
                                foreach ($views as $view) {
                                    if (isset($view['duration_ms']) && $view['duration_ms'] !== null) {
                                        $totalDuration += $view['duration_ms'];
                                        $viewsWithDuration++;
                                    }
                                }
                                ?>
                                <div class="debugbar-stat-card">
                                    <div class="debugbar-stat-card-label">Total Views</div>
                                    <div class="debugbar-stat-card-value"><?= count($views) ?></div>
                                </div>
                                <?php if ($viewsWithDuration > 0): ?>
                                    <div class="debugbar-stat-card">
                                        <div class="debugbar-stat-card-label">Total Duration</div>
                                        <div class="debugbar-stat-card-value"><?= round($totalDuration, 2) ?>ms</div>
                                    </div>
                                    <div class="debugbar-stat-card">
                                        <div class="debugbar-stat-card-label">Average Duration</div>
                                        <div class="debugbar-stat-card-value"><?= round($totalDuration / $viewsWithDuration, 2) ?>ms</div>
                                    </div>
                                <?php endif; ?>
                                <?php foreach ($viewTypes as $type => $count): ?>
                                    <div class="debugbar-stat-card">
                                        <div class="debugbar-stat-card-label"><?= ucfirst($type) ?></div>
                                        <div class="debugbar-stat-card-value"><?= $count ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Views List -->
                    <div class="debugbar-section" data-section="views-list">
                        <div class="debugbar-section-title">
                            <span>📄 Rendered Views (<?= count($views) ?>)</span>
                            <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="debugbar-section-content">
                            <?php foreach ($views as $view): ?>
                                <div class="debugbar-list-item">
                                    <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                                        <span class="debugbar-badge debugbar-badge-primary">
                                            <?= ucfirst($view['type']) ?>
                                        </span>
                                        <span class="debugbar-list-item-label"><?= htmlspecialchars(str_replace('\\', '/', $view['name'])) ?>.php</span>
                                    </div>
                                    <?php if (isset($view['duration_ms']) && $view['duration_ms'] !== null): ?>
                                        <span class="debugbar-list-item-value"><?= $view['duration_ms'] ?>ms</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="debugbar-empty">
                        <div class="debugbar-empty-icon">📄</div>
                        <div class="debugbar-empty-title">No views rendered</div>
                        <div class="debugbar-empty-desc">Views will appear here when they are rendered</div>
                    </div>
                <?php endif; ?>
            </div>

        <!-- Request Panel -->
        <div class="debugbar-panel" data-panel="request">
            <!-- Basic Request Info -->
            <div class="debugbar-section" data-section="request-basic">
                <div class="debugbar-section-title">
                    <span>ℹ️ Basic Request Info</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <div class="debugbar-info-grid">
                        <div class="debugbar-info-item">
                            <div class="debugbar-info-label">Method</div>
                            <div class="debugbar-info-value">
                                <span class="debugbar-badge <?= strtolower($request['method']) === 'get' ? 'debugbar-badge-success' : 'debugbar-badge-warning' ?>">
                                    <?= htmlspecialchars($request['method']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="debugbar-info-item">
                            <div class="debugbar-info-label">URI</div>
                            <div class="debugbar-info-value"><?= htmlspecialchars($request['uri']) ?></div>
                        </div>
                        <div class="debugbar-info-item">
                            <div class="debugbar-info-label">Controller</div>
                            <div class="debugbar-info-value"><?= htmlspecialchars($request['controller']) ?></div>
                        </div>
                        <div class="debugbar-info-item">
                            <div class="debugbar-info-label">Action</div>
                            <div class="debugbar-info-value"><?= htmlspecialchars($request['action']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Headers -->
            <div class="debugbar-section" data-section="request-headers">
                <div class="debugbar-section-title">
                    <span>📋 Headers (<?= !empty($request['headers']) ? count($request['headers']) : 0 ?>)</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <?php if (!empty($request['headers'])): ?>
                        <?php foreach ($request['headers'] as $key => $value): ?>
                            <div class="debugbar-key-value-item">
                                <div class="debugbar-key-value-key"><?= htmlspecialchars($key) ?></div>
                                <div class="debugbar-key-value-value"><?= htmlspecialchars($value) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="debugbar-empty">
                            <div class="debugbar-empty-title">No headers available</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>


            <!-- GET Parameters -->
            <?php if (!empty($_GET)): ?>
            <div class="debugbar-section" data-section="request-get">
                <div class="debugbar-section-title">
                    <span>🔍 GET Parameters (<?= count($_GET) ?>)</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <div class="debugbar-info-grid">
                        <?php foreach ($_GET as $key => $value): ?>
                            <div class="debugbar-info-item">
                                <div class="debugbar-info-label"><?= htmlspecialchars($key) ?></div>
                                <div class="debugbar-info-value"><?= htmlspecialchars($value) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- POST Data -->
            <?php if (!empty($_POST)): ?>
            <div class="debugbar-section" data-section="request-post">
                <div class="debugbar-section-title">
                    <span>📝 POST Data (<?= count($_POST) ?>)</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <div class="debugbar-info-grid">
                        <?php foreach ($_POST as $key => $value): ?>
                            <div class="debugbar-info-item">
                                <div class="debugbar-info-label"><?= htmlspecialchars($key) ?></div>
                                <div class="debugbar-info-value">
                                    <?php if (is_array($value) || is_object($value)): ?>
                                        <pre style="margin: 0; font-size: 0.75rem; font-family: 'JetBrains Mono', 'Consolas', monospace;"><?= htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) ?></pre>
                                    <?php else: ?>
                                        <?= htmlspecialchars($value) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Cookies -->
            <?php if (!empty($_COOKIE)): ?>
            <div class="debugbar-section" data-section="request-cookies">
                <div class="debugbar-section-title">
                    <span>🍪 Cookies (<?= count($_COOKIE) ?>)</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <div class="debugbar-info-grid">
                        <?php foreach ($_COOKIE as $name => $value): ?>
                            <div class="debugbar-info-item">
                                <div class="debugbar-info-label"><?= htmlspecialchars($name) ?></div>
                                <div class="debugbar-info-value">
                                    <?php if (is_array($value) || is_object($value)): ?>
                                        <pre style="margin: 0; font-size: 0.75rem; font-family: 'JetBrains Mono', 'Consolas', monospace;"><?= htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT)) ?></pre>
                                    <?php else: ?>
                                        <?= htmlspecialchars($value) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Environment Panel -->
        <div class="debugbar-panel" data-panel="environment">

            <!-- Performance Info -->
            <div class="debugbar-section" data-section="env-performance">
                <div class="debugbar-section-title">
                    <span>⚡ Performance Info</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <div class="debugbar-stats-grid">
                        <div class="debugbar-stat-card">
                            <div class="debugbar-stat-card-label">Memory Usage</div>
                            <div class="debugbar-stat-card-value">
                                <?= \System\Libraries\Monitor::formatMemorySize(memory_get_usage()) ?>
                            </div>
                        </div>
                        <div class="debugbar-stat-card">
                            <div class="debugbar-stat-card-label">Peak Memory</div>
                            <div class="debugbar-stat-card-value">
                                <?= \System\Libraries\Monitor::formatMemorySize(memory_get_peak_usage()) ?>
                            </div>
                        </div>
                        <div class="debugbar-stat-card">
                            <div class="debugbar-stat-card-label">Memory Limit</div>
                            <div class="debugbar-stat-card-value">
                                <?= ini_get('memory_limit') ?>
                            </div>
                        </div>
                        <div class="debugbar-stat-card">
                            <div class="debugbar-stat-card-label">Max Execution Time</div>
                            <div class="debugbar-stat-card-value">
                                <?= ini_get('max_execution_time') ?>s
                            </div>
                        </div>
                        <div class="debugbar-stat-card">
                            <div class="debugbar-stat-card-label">Upload Max Filesize</div>
                            <div class="debugbar-stat-card-value">
                                <?= ini_get('upload_max_filesize') ?>
                            </div>
                        </div>
                        <div class="debugbar-stat-card">
                            <div class="debugbar-stat-card-label">Post Max Size</div>
                            <div class="debugbar-stat-card-value">
                                <?= ini_get('post_max_size') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Info -->
            <div class="debugbar-section" data-section="env-app">
                <div class="debugbar-section-title">
                    <span>🚀 Application Info</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <div class="debugbar-info-grid">
                        <div class="debugbar-info-item">
                            <div class="debugbar-info-label">App Name</div>
                            <div class="debugbar-info-value"><?= htmlspecialchars($environment['app_name']) ?></div>
                        </div>
                        <div class="debugbar-info-item">
                            <div class="debugbar-info-label">Debug Mode</div>
                            <div class="debugbar-info-value">
                                <span class="debugbar-badge <?= $environment['debug'] ? 'debugbar-badge-success' : 'debugbar-badge-error' ?>">
                                    <?= $environment['debug'] ? 'Enabled' : 'Disabled' ?>
                                </span>
                            </div>
                        </div>
                        <div class="debugbar-info-item">
                            <div class="debugbar-info-label">PHP Version</div>
                            <div class="debugbar-info-value"><?= htmlspecialchars($environment['php_version']) ?></div>
                        </div>
                        <div class="debugbar-info-item">
                            <div class="debugbar-info-label">Memory Limit</div>
                            <div class="debugbar-info-value"><?= htmlspecialchars($environment['memory_limit']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PHP Extensions -->
            <div class="debugbar-section" data-section="env-extensions">
                <div class="debugbar-section-title">
                    <span>🔧 PHP Extensions (<?= count($environment['loaded_extensions']) ?>)</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <div class="debugbar-info-grid">
                        <?php foreach ($environment['loaded_extensions'] as $ext): ?>
                            <div class="debugbar-info-item">
                                <div class="debugbar-info-value">
                                    <span class="debugbar-badge debugbar-badge-primary"><?= htmlspecialchars($ext) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Server Information -->
            <div class="debugbar-section" data-section="env-server">
                <div class="debugbar-section-title">
                    <span>🖥️ Server Information</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <div class="debugbar-info-grid">
                        <?php
                        $serverInfo = [
                            'HTTP_HOST' => 'Host',
                            'SERVER_NAME' => 'Server Name',
                            'SERVER_PORT' => 'Port',
                            'SERVER_SOFTWARE' => 'Software',
                            'SERVER_PROTOCOL' => 'Protocol',
                            'REQUEST_SCHEME' => 'Scheme',
                            'HTTPS' => 'HTTPS',
                            'REMOTE_ADDR' => 'Client IP',
                            'HTTP_USER_AGENT' => 'User Agent'
                        ];

                        foreach ($serverInfo as $key => $label):
                            if (isset($_SERVER[$key])):
                        ?>
                                <div class="debugbar-info-item">
                                    <div class="debugbar-info-label"><?= htmlspecialchars($label) ?></div>
                                    <div class="debugbar-info-value" style="<?= $key === 'HTTP_USER_AGENT' ? 'word-break: break-all;' : '' ?>">
                                        <?= htmlspecialchars($_SERVER[$key]) ?>
                                    </div>
                                </div>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            </div>


            <!-- Raw Server Data -->
            <div class="debugbar-section" data-section="env-raw">
                <div class="debugbar-section-title">
                    <span>📄 Raw Server Data</span>
                    <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
                <div class="debugbar-section-content">
                    <div class="debugbar-query-sql">
                        <?= htmlspecialchars(json_encode($_SERVER, JSON_PRETTY_PRINT)) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hooks Panel -->
        <div class="debugbar-panel" data-panel="hooks">
            <?php if (!empty($hooks_data['hooks']) || !empty($hooks_history)): ?>
                
                <!-- Current Request Hooks -->
                <?php if (!empty($hooks_data['hooks'])): ?>
                    <!-- Hooks Summary -->
                    <div class="debugbar-section" data-section="hooks-summary">
                        <div class="debugbar-section-title">
                            <span>📊 Current Request Hooks Summary</span>
                            <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="debugbar-section-content">
                            <div class="debugbar-stats-grid">
                                <div class="debugbar-stat-card">
                                    <div class="debugbar-stat-card-label">Total Hooks</div>
                                    <div class="debugbar-stat-card-value"><?= $hooks_data['summary']['total_hooks'] ?></div>
                                </div>
                                <div class="debugbar-stat-card">
                                    <div class="debugbar-stat-card-label">Total Executions</div>
                                    <div class="debugbar-stat-card-value"><?= $hooks_data['summary']['total_executions'] ?></div>
                                </div>
                                <div class="debugbar-stat-card">
                                    <div class="debugbar-stat-card-label">Total Callbacks</div>
                                    <div class="debugbar-stat-card-value"><?= $hooks_data['summary']['total_callbacks'] ?></div>
                                </div>
                                <div class="debugbar-stat-card">
                                    <div class="debugbar-stat-card-label">Total Time</div>
                                    <div class="debugbar-stat-card-value"><?= $hooks_data['summary']['total_time_ms'] ?>ms</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hooks Execution List -->
                    <div class="debugbar-section" data-section="hooks-list">
                        <div class="debugbar-section-title">
                            <span>🔥 Hooks Execution (Click to view details)</span>
                            <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="debugbar-section-content">
                            <?php 
                            $hookIndex = 0;
                            foreach ($hooks_data['detailed_log'] as $log): 
                                if (isset($log['is_callback']) && $log['is_callback']) continue; // Skip callbacks, only show main hooks
                                $hookIndex++;
                            ?>
                                <div class="debugbar-query" style="margin-bottom: 0.75rem;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: hsl(var(--muted, 210 40% 96.1%) / 0.3); border-radius: 0.375rem; cursor: pointer; transition: all 0.2s;" 
                                         onclick="toggleHookDetails(<?= $hookIndex ?>)"
                                         onmouseover="this.style.background='hsl(var(--muted, 210 40% 96.1%) / 0.5)'"
                                         onmouseout="this.style.background='hsl(var(--muted, 210 40% 96.1%) / 0.3)'">
                                        <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                                            <svg id="hook-icon-<?= $hookIndex ?>" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                                                <polyline points="9 18 15 12 9 6"></polyline>
                                            </svg>
                                            <span class="debugbar-badge <?= $log['type'] === 'action' ? 'debugbar-badge-primary' : 'debugbar-badge-warning' ?>">
                                                <?= strtoupper($log['type']) ?>
                                            </span>
                                            <span style="font-size: 0.875rem; font-weight: 600; color: hsl(var(--foreground, 222.2 84% 4.9%));">
                                                <?= htmlspecialchars($log['hook']) ?>
                                            </span>
                                            <span class="debugbar-badge debugbar-badge-success">
                                                <?= $log['callbacks'] ?> callbacks
                                            </span>
                                            <span class="debugbar-badge debugbar-badge-primary">
                                                <?= $log['args_count'] ?> args
                                            </span>
                                        </div>
                                        <span class="debugbar-badge debugbar-badge-success"><?= round($log['time'] * 1000, 3) ?>ms</span>
                                    </div>
                                    
                                    <!-- Hook Details (Hidden by default) -->
                                    <div id="hook-details-<?= $hookIndex ?>" style="display: none; margin-top: 0.75rem; padding: 0.75rem; background: hsl(var(--background, 0 0% 100%)); border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%)); border-radius: 0.375rem;">
                                        <!-- Caller Info -->
                                        <?php if (isset($log['caller_file']) && $log['caller_file'] !== 'unknown'): ?>
                                            <div style="margin-bottom: 0.75rem; padding: 0.5rem; background: hsl(var(--muted, 210 40% 96.1%) / 0.2); border-radius: 0.25rem; border-left: 3px solid hsl(var(--primary, 221.2 83.2% 53.3%));">
                                                <div style="font-size: 0.7rem; font-weight: 600; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%)); margin-bottom: 0.25rem;">
                                                    📍 Called from:
                                                </div>
                                                <div style="font-size: 0.7rem; font-family: 'JetBrains Mono', 'Consolas', monospace; color: hsl(var(--foreground, 222.2 84% 4.9%));">
                                                    <?= htmlspecialchars($log['caller_file']) ?>:<span style="color: hsl(var(--primary, 221.2 83.2% 53.3%)); font-weight: 600;"><?= $log['caller_line'] ?></span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Arguments -->
                                        <?php if (!empty($log['args']) && $log['args_count'] > 0): ?>
                                            <div style="margin-bottom: 0.75rem;">
                                                <div style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem; color: hsl(var(--foreground, 222.2 84% 4.9%));">
                                                    <?= $log['type'] === 'filter' ? '📥 Input Value & Arguments' : '📥 Arguments' ?> (<?= $log['args_count'] ?>):
                                                </div>
                                                <?php foreach ($log['args'] as $argIndex => $argData): ?>
                                                    <div style="margin-bottom: 0.5rem; padding: 0.5rem; background: hsl(var(--muted, 210 40% 96.1%) / 0.2); border-radius: 0.25rem;">
                                                        <div style="margin-bottom: 0.25rem;">
                                                            <strong style="color: hsl(var(--primary, 221.2 83.2% 53.3%)); font-size: 0.75rem;">
                                                                <?= $log['type'] === 'filter' && $argIndex === 0 ? '💎 Value to Filter' : 'Arg ' . $argIndex ?>:
                                                            </strong>
                                                            <span class="debugbar-badge debugbar-badge-primary" style="font-size: 0.65rem;"><?= $argData['type'] ?></span>
                                                            <?php if ($argData['type'] === 'string'): ?>
                                                                <span style="font-size: 0.7rem; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));">(<?= $argData['length'] ?> chars)</span>
                                                            <?php elseif ($argData['type'] === 'array'): ?>
                                                                <span style="font-size: 0.7rem; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));">(<?= $argData['count'] ?> items)</span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div style="font-size: 0.7rem; font-family: 'JetBrains Mono', 'Consolas', monospace; color: hsl(var(--foreground, 222.2 84% 4.9%)); max-height: 100px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; background: hsl(var(--background, 0 0% 100%)); padding: 0.5rem; border-radius: 0.25rem;">
<?php if ($argData['type'] === 'string'): ?>
<?= htmlspecialchars($argData['value']) ?>
<?php elseif ($argData['type'] === 'array'): ?>
<?= htmlspecialchars($argData['preview']) ?>
<?php elseif ($argData['type'] === 'object'): ?>
Class: <?= htmlspecialchars($argData['class']) ?>
<?php if (!empty($argData['methods'])): ?>
Methods: <?= htmlspecialchars(implode(', ', array_slice($argData['methods'], 0, 10))) ?><?= count($argData['methods']) > 10 ? '...' : '' ?>
<?php endif; ?>
<?php elseif ($argData['type'] === 'boolean'): ?>
<?= $argData['value'] ? 'true' : 'false' ?>
<?php elseif ($argData['type'] === 'NULL'): ?>
null
<?php elseif ($argData['type'] === 'resource'): ?>
Resource: <?= isset($argData['resource_type']) ? htmlspecialchars($argData['resource_type']) : 'unknown' ?>
<?php else: ?>
<?= htmlspecialchars(json_encode($argData['value'])) ?>
<?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Filter Output -->
                                        <?php if ($log['type'] === 'filter' && isset($log['after_value'])): ?>
                                            <div style="padding: 0.75rem; background: hsl(142.1 76.2% 36.3% / 0.1); border: 1px solid hsl(142.1 76.2% 36.3% / 0.2); border-radius: 0.375rem;">
                                                <div style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.5rem; color: hsl(142.1 76.2% 36.3%);">
                                                    📤 Final Output Value
                                                    <?php if (isset($log['value_changed'])): ?>
                                                        <span class="debugbar-badge <?= $log['value_changed'] ? 'debugbar-badge-warning' : 'debugbar-badge-success' ?>" style="font-size: 0.65rem;">
                                                            <?= $log['value_changed'] ? 'MODIFIED' : 'UNCHANGED' ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size: 0.7rem; font-family: 'JetBrains Mono', 'Consolas', monospace; max-height: 100px; overflow-y: auto; white-space: pre-wrap; background: hsl(var(--background, 0 0% 100%)); padding: 0.5rem; border-radius: 0.25rem;">
<?= htmlspecialchars(json_encode($log['after_value'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Note -->
                                        <?php if (!empty($log['note'])): ?>
                                            <div style="font-size: 0.75rem; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%)); margin-top: 0.5rem; font-style: italic;">
                                                ℹ️ <?= htmlspecialchars($log['note']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                <?php endif; ?>

                <!-- Previous Requests Hooks History -->
                <?php if (!empty($hooks_history)): ?>
                    <div class="debugbar-section" data-section="hooks-history">
                        <div class="debugbar-section-title">
                            <span>📜 Previous Requests (<?= count($hooks_history) ?>)</span>
                            <svg class="icon-expanded" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            <svg class="icon-collapsed" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="debugbar-section-content">
                            <?php foreach ($hooks_history as $historyIndex => $historyData): ?>
                                <div style="margin-bottom: 1.5rem; padding: 1rem; background: hsl(var(--muted, 210 40% 96.1%) / 0.2); border-radius: 0.5rem; border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));">
                                    <!-- Request Header -->
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.75rem; border-bottom: 2px solid hsl(var(--border, 214.3 31.8% 91.4%));">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <span style="font-weight: 700; color: hsl(var(--primary, 221.2 83.2% 53.3%));">Request #<?= $historyIndex + 1 ?></span>
                                            <span class="debugbar-badge debugbar-badge-primary">
                                                <?= htmlspecialchars($historyData['request_info']['method'] ?? 'GET') ?>
                                            </span>
                                            <span style="font-size: 0.875rem; font-weight: 500;">
                                                <?= htmlspecialchars($historyData['request_info']['uri'] ?? '/') ?>
                                            </span>
                                        </div>
                                        <span style="font-size: 0.75rem; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));">
                                            <?= $historyData['request_info']['timestamp'] ?? '' ?>
                                        </span>
                                    </div>
                                    
                                    <!-- Summary Stats -->
                                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 0.75rem;">
                                        <div class="debugbar-stat-card" style="padding: 0.5rem;">
                                            <div class="debugbar-stat-card-label">Hooks</div>
                                            <div class="debugbar-stat-card-value" style="font-size: 1.25rem;">
                                                <?= $historyData['summary']['total_hooks'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="debugbar-stat-card" style="padding: 0.5rem;">
                                            <div class="debugbar-stat-card-label">Executions</div>
                                            <div class="debugbar-stat-card-value" style="font-size: 1.25rem;">
                                                <?= $historyData['summary']['total_executions'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="debugbar-stat-card" style="padding: 0.5rem;">
                                            <div class="debugbar-stat-card-label">Callbacks</div>
                                            <div class="debugbar-stat-card-value" style="font-size: 1.25rem;">
                                                <?= $historyData['summary']['total_callbacks'] ?? 0 ?>
                                            </div>
                                        </div>
                                        <div class="debugbar-stat-card" style="padding: 0.5rem;">
                                            <div class="debugbar-stat-card-label">Time</div>
                                            <div class="debugbar-stat-card-value" style="font-size: 1.25rem;">
                                                <?= $historyData['summary']['total_time_ms'] ?? 0 ?>ms
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hooks List (Clickable) -->
                                    <?php if (!empty($historyData['detailed_log'])): ?>
                                        <div style="font-size: 0.8125rem; font-weight: 600; margin-bottom: 0.5rem; color: hsl(var(--foreground, 222.2 84% 4.9%));">
                                            Hooks Executed (Click to view details):
                                        </div>
                                        <?php 
                                        $historyHookIndex = 0;
                                        foreach ($historyData['detailed_log'] as $histLog): 
                                            if (isset($histLog['is_callback']) && $histLog['is_callback']) continue; // Skip callbacks, only show main hooks
                                            $historyHookIndex++;
                                            $uniqueId = 'history-' . $historyIndex . '-hook-' . $historyHookIndex;
                                        ?>
                                            <div style="margin-bottom: 0.5rem;">
                                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; background: hsl(var(--muted, 210 40% 96.1%) / 0.3); border-radius: 0.25rem; cursor: pointer; transition: all 0.2s;"
                                                     onclick="toggleHookDetails('<?= $uniqueId ?>')"
                                                     onmouseover="this.style.background='hsl(var(--muted, 210 40% 96.1%) / 0.5)'"
                                                     onmouseout="this.style.background='hsl(var(--muted, 210 40% 96.1%) / 0.3)'">
                                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                                        <svg id="hook-icon-<?= $uniqueId ?>" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform 0.2s;">
                                                            <polyline points="9 18 15 12 9 6"></polyline>
                                                        </svg>
                                                        <span class="debugbar-badge <?= $histLog['type'] === 'action' ? 'debugbar-badge-primary' : 'debugbar-badge-warning' ?>" style="font-size: 0.65rem; padding: 0.2rem 0.5rem;">
                                                            <?= strtoupper($histLog['type']) ?>
                                                        </span>
                                                        <span style="font-size: 0.8rem; font-weight: 500;">
                                                            <?= htmlspecialchars($histLog['hook']) ?>
                                                        </span>
                                                        <span style="font-size: 0.7rem; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));">
                                                            <?= $histLog['callbacks'] ?> callbacks
                                                        </span>
                                                        <span class="">
                                                            <?= $histLog['args_count'] ?> args
                                                        </span>
                                                    </div>
                                                    <span style="font-size: 0.7rem; font-weight: 600; color: hsl(var(--primary, 221.2 83.2% 53.3%));">
                                                        <?= round($histLog['time'] * 1000, 3) ?>ms
                                                    </span>
                                                </div>
                                                
                                                <!-- Hook Details -->
                                                <div id="hook-details-<?= $uniqueId ?>" style="display: none; margin-top: 0.5rem; padding: 0.75rem; background: hsl(var(--background, 0 0% 100%)); border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%)); border-radius: 0.25rem;">
                                                    <!-- Caller Info -->
                                                    <?php if (isset($histLog['caller_file']) && $histLog['caller_file'] !== 'unknown'): ?>
                                                        <div style="margin-bottom: 0.5rem; padding: 0.375rem; background: hsl(var(--muted, 210 40% 96.1%) / 0.2); border-radius: 0.25rem; border-left: 2px solid hsl(var(--primary, 221.2 83.2% 53.3%));">
                                                            <div style="font-size: 0.65rem; font-weight: 600; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%)); margin-bottom: 0.125rem;">
                                                                📍 Called from:
                                                            </div>
                                                            <div style="font-size: 0.65rem; font-family: 'JetBrains Mono', 'Consolas', monospace; color: hsl(var(--foreground, 222.2 84% 4.9%));">
                                                                <?= htmlspecialchars($histLog['caller_file']) ?>:<span style="color: hsl(var(--primary, 221.2 83.2% 53.3%)); font-weight: 600;"><?= $histLog['caller_line'] ?></span>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Arguments -->
                                                    <?php if (!empty($histLog['args']) && $histLog['args_count'] > 0): ?>
                                                        <div style="margin-bottom: 0.75rem;">
                                                            <div style="font-size: 0.7rem; font-weight: 600; margin-bottom: 0.5rem;">
                                                                <?= $histLog['type'] === 'filter' ? '📥 Input' : '📥 Arguments' ?> (<?= $histLog['args_count'] ?>):
                                                            </div>
                                                            <?php foreach ($histLog['args'] as $argIdx => $argD): ?>
                                                                <div style="margin-bottom: 0.375rem; padding: 0.375rem; background: hsl(var(--muted, 210 40% 96.1%) / 0.2); border-radius: 0.25rem; font-size: 0.7rem;">
                                                                    <strong style="color: hsl(var(--primary, 221.2 83.2% 53.3%));">
                                                                        <?= $histLog['type'] === 'filter' && $argIdx === 0 ? '💎 Value' : 'Arg ' . $argIdx ?>:
                                                                    </strong>
                                                                    <span class="debugbar-badge debugbar-badge-primary" style="font-size: 0.6rem;"><?= $argD['type'] ?></span>
                                                                    <?php if ($argD['type'] === 'string'): ?>
                                                                        (<?= $argD['length'] ?> chars)
                                                                        <div style="margin-top: 0.25rem; font-family: 'JetBrains Mono', 'Consolas', monospace; word-break: break-all;">
                                                                            <?= htmlspecialchars(mb_strlen($argD['value']) > 100 ? mb_substr($argD['value'], 0, 100) . '...' : $argD['value']) ?>
                                                                        </div>
                                                                    <?php elseif ($argD['type'] === 'array'): ?>
                                                                        (<?= $argD['count'] ?> items)
                                                                        <div style="margin-top: 0.25rem; font-family: 'JetBrains Mono', 'Consolas', monospace; font-size: 0.65rem;">
                                                                            <?= htmlspecialchars(mb_strlen($argD['preview']) > 150 ? mb_substr($argD['preview'], 0, 150) . '...' : $argD['preview']) ?>
                                                                        </div>
                                                                    <?php elseif ($argD['type'] === 'object'): ?>
                                                                        <div style="margin-top: 0.25rem;"><?= htmlspecialchars($argD['class']) ?></div>
                                                                    <?php else: ?>
                                                                        <?= htmlspecialchars(json_encode($argD['value'])) ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Filter Output -->
                                                    <?php if ($histLog['type'] === 'filter' && isset($histLog['after_value'])): ?>
                                                        <div style="padding: 0.5rem; background: hsl(142.1 76.2% 36.3% / 0.1); border: 1px solid hsl(142.1 76.2% 36.3% / 0.2); border-radius: 0.25rem;">
                                                            <div style="font-size: 0.7rem; font-weight: 600; margin-bottom: 0.25rem; color: hsl(142.1 76.2% 36.3%);">
                                                                📤 Output 
                                                                <?php if (isset($histLog['value_changed'])): ?>
                                                                    <span class="debugbar-badge <?= $histLog['value_changed'] ? 'debugbar-badge-warning' : 'debugbar-badge-success' ?>" style="font-size: 0.6rem;">
                                                                        <?= $histLog['value_changed'] ? 'MODIFIED' : 'UNCHANGED' ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div style="font-size: 0.65rem; font-family: 'JetBrains Mono', 'Consolas', monospace; max-height: 80px; overflow-y: auto;">
                                                                <?= htmlspecialchars(json_encode($histLog['after_value'], JSON_UNESCAPED_UNICODE)) ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="debugbar-empty">
                    <div class="debugbar-empty-icon">🎣</div>
                    <div class="debugbar-empty-title">No hooks executed</div>
                    <div class="debugbar-empty-desc">Hooks (actions & filters) will appear here when they are executed</div>
                </div>
            <?php endif; ?>
        </div>

            <!-- Cache Panel -->
            <div class="debugbar-panel" data-panel="cache">
                <?php if (!empty($debug_cache)): ?>
                    <div class="debugbar-section">
                        <div class="debugbar-section-title">💾 Cache Operations (<?= count($debug_cache) ?>)</div>
                        <div class="debugbar-section-content">
                            <?php foreach ($debug_cache as $operation): ?>
                                <div class="debugbar-list-item">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <span class="debugbar-badge <?= $operation['success'] ? 'debugbar-badge-success' : 'debugbar-badge-error' ?>">
                                            <?= strtoupper($operation['operation']) ?>
                                        </span>
                                        <span class="debugbar-list-item-label"><?= htmlspecialchars($operation['key']) ?></span>
                                    </div>
                                    <span class="debugbar-list-item-value"><?= $operation['execution_time'] ?>ms</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="debugbar-empty">
                        <div class="debugbar-empty-icon">💾</div>
                        <div class="debugbar-empty-title">No cache operations</div>
                        <div class="debugbar-empty-desc">Cache operations will appear here when they are performed</div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Logs Panel -->
            <div class="debugbar-panel" data-panel="logs">
                <?php if (!empty($logs)): ?>
                    <div class="debugbar-section">
                        <div class="debugbar-section-title">📝 Message Logs (<?= count($logs) ?>)</div>
                        <div class="debugbar-section-content">
                            <?php foreach ($logs as $i => $log): ?>
                                <div class="debugbar-query">
                                    <div class="debugbar-query-header">
                                        <div class="debugbar-query-info">
                                            <span class="debugbar-badge debugbar-badge-<?= strtolower($log['level']) === 'error' ? 'error' : (strtolower($log['level']) === 'warning' ? 'warning' : 'primary') ?>">
                                                <?= htmlspecialchars($log['level']) ?>
                                            </span>
                                            <?php if ($log['is_json']): ?>
                                                <span class="debugbar-badge debugbar-badge-primary">JSON</span>
                                            <?php endif; ?>
                                        </div>
                                        <span style="font-size: 0.75rem; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));"><?= $log['timestamp'] ?></span>
                                    </div>

                                    <?php if ($log['is_json']): ?>
                                        <div class="debugbar-query-sql">
                                            <?= htmlspecialchars(json_encode($log['json_data'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="debugbar-query-sql">
                                            <?= htmlspecialchars($log['message']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($log['file'] && $log['line']): ?>
                                        <div style="margin-top: 0.75rem; font-size: 0.75rem; color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%)); font-family: 'JetBrains Mono', 'Consolas', monospace;">
                                            <strong>Location:</strong> <?= htmlspecialchars($log['file']) ?>:<?= $log['line'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="debugbar-empty">
                        <div class="debugbar-empty-icon">📝</div>
                        <div class="debugbar-empty-title">No logs recorded</div>
                        <div class="debugbar-empty-desc">Logs will appear here when Logger methods are called</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<?php if ($db_js !== ''): ?>
<script src="<?= htmlspecialchars($db_js, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endif; ?>
