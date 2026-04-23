<?php
use System\Database\DB;
if (defined('APP_DEBUGBAR_SKIP') && APP_DEBUGBAR_SKIP) {
    return;
}
$debug_sql = DB::getQueryLog();


global $debug_cache;
// Get data for debugbar
$performance = \System\Libraries\Monitor::endFramework();
$profiles = \System\Libraries\Monitor::getProfiles();
$views = \System\Libraries\Render::getViews();
$logs = \System\Libraries\Logger::getLogs();
$hooks_data = \System\Libraries\Hooks::getDebugBarData();
$hooks_history = \System\Libraries\Hooks::getHistory();
$request = [
    'method'     => $_SERVER['REQUEST_METHOD'] ?? '',
    'uri'        => '/' . APP_URI['uri'] ?? '',
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

?>
<style>
    /* Debug Panel - Backend Theme Inspired */
    #debugbar {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        background: #f2f2f2;
        border-top: 1px solid #e5e5e5;
        display: flex;
        flex-direction: column;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: calc(100vh - 60px);
    }

    .debugbar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.3rem 1.25rem;
        background: hsl(var(--muted, 210 40% 96.1%));
        border-bottom: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        cursor: pointer;
        user-select: none;
        transition: background 0.2s;
        flex-shrink: 0;
        min-height: 48px;
    }

    .debugbar-header:hover {
        background: hsl(var(--accent, 210 40% 96.1%));
    }

    .debugbar-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        font-size: 0.875rem;
        color: hsl(var(--foreground, 222.2 84% 4.9%));
    }

    .debugbar-stats {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        font-size: 0.75rem;
        color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));
    }

    .debugbar-stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.75rem;
        background: hsl(var(--background, 0 0% 100%));
        border-radius: 0.375rem;
        border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
    }

    .debugbar-stat-label {
        font-weight: 500;
    }

    .debugbar-stat-value {
        font-weight: 600;
        color: hsl(var(--primary, 221.2 83.2% 53.3%));
    }

    .debugbar-toggle-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.375rem;
        background: transparent;
        border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));
        cursor: pointer;
        transition: all 0.2s;
    }

    .debugbar-toggle-btn:hover {
        background: hsl(var(--accent, 210 40% 96.1%));
        color: hsl(var(--foreground, 222.2 84% 4.9%));
    }

    .debugbar-content {
        display: flex;
        flex-direction: column;
        flex: 1;
        overflow: hidden;
    }

    .debugbar-tabs {
        display: flex;
        gap: 0.25rem;
        padding: 0.5rem 1rem;
        background: hsl(var(--muted, 210 40% 96.1%));
        border-bottom: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        overflow-x: auto;
        flex-shrink: 0;
        min-height: 48px;
    }

    .debugbar-tab {
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));
        background: transparent;
        border: none;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .debugbar-tab:hover {
        background: hsl(var(--accent, 210 40% 96.1%));
        color: hsl(var(--foreground, 222.2 84% 4.9%));
    }

    .debugbar-tab.active {
        background: hsl(var(--background, 0 0% 100%));
        color: hsl(var(--primary, 221.2 83.2% 53.3%));
        font-weight: 600;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    }

    .debugbar-panel {
        display: none;
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 1.25rem;
        background: hsl(var(--background, 0 0% 100%));
        min-height: 0;
    }

    .debugbar-panel.active {
        display: block;
    }

    .debugbar-section {
        margin-bottom: 1.5rem;
        background: hsl(var(--card, 0 0% 100%));
        border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .debugbar-section-title {
        padding: 0.5rem 1.25rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: hsl(var(--foreground, 222.2 84% 4.9%));
        background: hsl(var(--muted, 210 40% 96.1%));
        border-bottom: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.2s;
    }

    .debugbar-section-title:hover {
        background: hsl(var(--accent, 210 40% 96.1%));
    }

    .debugbar-section-content {
        padding: 1.25rem;
    }

    .debugbar-section-content.collapsed {
        display: none;
    }

    /* SQL Query Styling */
    .debugbar-query {
        margin-bottom: 1rem;
        padding: 0.5rem 1rem;
        background: hsl(var(--muted, 210 40% 96.1%) / 0.3);
        border-left: 3px solid hsl(var(--primary, 221.2 83.2% 53.3%));
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .debugbar-query:hover {
        background: hsl(var(--muted, 210 40% 96.1%) / 0.5);
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
    }

    .debugbar-query-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.75rem;
        flex-wrap: wrap;
    }

    .debugbar-query-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
        flex: 1;
    }

    .debugbar-query-number {
        font-weight: 600;
        font-size: 0.875rem;
        color: hsl(var(--primary, 221.2 83.2% 53.3%));
    }

    .debugbar-badge {
        padding: 0.25rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 0.375rem;
        white-space: nowrap;
    }

    .debugbar-badge-primary {
        background: hsl(var(--primary, 221.2 83.2% 53.3%) / 0.1);
        color: hsl(var(--primary, 221.2 83.2% 53.3%));
        border: 1px solid hsl(var(--primary, 221.2 83.2% 53.3%) / 0.2);
    }

    .debugbar-badge-success {
        background: hsl(142.1 76.2% 36.3% / 0.1);
        color: hsl(142.1 76.2% 36.3%);
        border: 1px solid hsl(142.1 76.2% 36.3% / 0.2);
    }

    .debugbar-badge-error {
        background: hsl(0 84.2% 60.2% / 0.1);
        color: hsl(0 84.2% 60.2%);
        border: 1px solid hsl(0 84.2% 60.2% / 0.2);
    }

    .debugbar-badge-warning {
        background: hsl(47.9 95.8% 53.1% / 0.1);
        color: hsl(47.9 95.8% 53.1%);
        border: 1px solid hsl(47.9 95.8% 53.1% / 0.2);
    }

    .debugbar-query-actions {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .debugbar-copy-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.75rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: hsl(var(--foreground, 222.2 84% 4.9%));
        background: hsl(var(--background, 0 0% 100%));
        border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        border-radius: 0.375rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .debugbar-copy-btn:hover {
        background: hsl(var(--accent, 210 40% 96.1%));
        border-color: hsl(var(--primary, 221.2 83.2% 53.3%) / 0.5);
    }

    .debugbar-copy-btn.copied {
        background: hsl(142.1 76.2% 36.3% / 0.1);
        color: hsl(142.1 76.2% 36.3%);
        border-color: hsl(142.1 76.2% 36.3%);
    }

    .debugbar-copy-btn svg {
        width: 0.875rem;
        height: 0.875rem;
    }

    .debugbar-query-sql {
        padding: 0px;
        background: hsl(var(--background, 0 0% 100%));
        border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        border-radius: 0.5rem;
        font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 0.8125rem;
        line-height: 1.6;
        color: hsl(var(--foreground, 222.2 84% 4.9%));
        overflow-x: auto;
        white-space: pre-wrap;
        word-break: break-all;
    }

    .debugbar-error-box {
        padding: 1rem;
        background: hsl(0 84.2% 60.2% / 0.05);
        border: 1px solid hsl(0 84.2% 60.2% / 0.2);
        border-left: 3px solid hsl(0 84.2% 60.2%);
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .debugbar-error-title {
        font-weight: 600;
        font-size: 0.8125rem;
        color: hsl(0 84.2% 60.2%);
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .debugbar-error-message {
        font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
        font-size: 0.75rem;
        color: hsl(var(--foreground, 222.2 84% 4.9%));
        line-height: 1.5;
    }

    /* Stats Grid */
    .debugbar-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .debugbar-stat-card {
        padding: 0.5rem;
        background: hsl(var(--muted, 210 40% 96.1%) / 0.3);
        border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        border-radius: 0.75rem;
        text-align: center;
        transition: all 0.2s;
    }

    .debugbar-stat-card:hover {
        background: hsl(var(--muted, 210 40% 96.1%) / 0.5);
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
        transform: translateY(-2px);
    }

    .debugbar-stat-card-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .debugbar-stat-card-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: hsl(var(--primary, 221.2 83.2% 53.3%));
    }

    /* Profile & Views Items */
    .debugbar-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        background: hsl(var(--muted, 210 40% 96.1%) / 0.3);
        border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .debugbar-list-item:hover {
        background: hsl(var(--muted, 210 40% 96.1%) / 0.5);
        border-color: hsl(var(--primary, 221.2 83.2% 53.3%) / 0.3);
    }

    .debugbar-list-item-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: hsl(var(--foreground, 222.2 84% 4.9%));
    }

    .debugbar-list-item-value {
        font-size: 0.8125rem;
        font-weight: 600;
        color: hsl(var(--primary, 221.2 83.2% 53.3%));
    }

    .profile-toggle-btn {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));
        transition: all 0.2s;
        border-radius: 0.25rem;
    }

    .profile-toggle-btn:hover {
        background: hsl(var(--accent, 210 40% 96.1%));
        color: hsl(var(--foreground, 222.2 84% 4.9%));
    }

    .profile-children {
        margin-left: 1.5rem;
    }

    /* Info Grid */
    .debugbar-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .debugbar-info-item {
        padding: 0.875rem 1rem;
        background: hsl(var(--muted, 210 40% 96.1%) / 0.3);
        border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        border-radius: 0.5rem;
    }

    .debugbar-info-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));
        letter-spacing: 0.05em;
        margin-bottom: 0.375rem;
    }

    .debugbar-info-value {
        font-size: 0.875rem;
        color: hsl(var(--foreground, 222.2 84% 4.9%));
        word-break: break-word;
    }

    /* Header/Server List Items */
    .debugbar-key-value-item {
        display: flex;
        align-items: flex-start;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        transition: all 0.2s;
    }

    .debugbar-key-value-item:hover {
        background: hsl(var(--muted, 210 40% 96.1%) / 0.3);
    }

    .debugbar-key-value-item:last-child {
        border-bottom: none;
    }

    .debugbar-key-value-key {
        min-width: 220px;
        font-weight: 600;
        font-size: 0.8125rem;
        color: hsl(var(--primary, 221.2 83.2% 53.3%));
        margin-right: 1.25rem;
        word-break: break-word;
        background: hsl(var(--muted, 210 40% 96.1%) / 0.5);
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
    }

    .debugbar-key-value-value {
        flex: 1;
        color: hsl(var(--foreground, 222.2 84% 4.9%));
        font-size: 0.8125rem;
        word-break: break-word;
        line-height: 1.5;
        font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
    }

    /* Empty State */
    .debugbar-empty {
        padding: 3rem 1.5rem;
        text-align: center;
        color: hsl(var(--muted-foreground, 215.4 16.3% 46.9%));
    }

    .debugbar-empty-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .debugbar-empty-title {
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .debugbar-empty-desc {
        font-size: 0.875rem;
        opacity: 0.75;
    }

    /* Scrollbar */
    .debugbar-panel::-webkit-scrollbar {
        width: 8px;
    }

    .debugbar-panel::-webkit-scrollbar-track {
        background: hsl(var(--muted, 210 40% 96.1%));
    }

    .debugbar-panel::-webkit-scrollbar-thumb {
        background: hsl(var(--border, 214.3 31.8% 91.4%));
        border-radius: 4px;
    }

    .debugbar-panel::-webkit-scrollbar-thumb:hover {
        background: hsl(var(--muted-foreground, 215.4 16.3% 46.9%) / 0.5);
    }

    /* Resize Handle */
    .debugbar-resize-handle {
        height: 3px;
        background: linear-gradient(90deg, hsl(var(--primary, 221.2 83.2% 53.3%)), hsl(var(--primary, 221.2 83.2% 53.3%) / 0.7));
        cursor: ns-resize;
        position: relative;
        z-index: 10000;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 1;
        visibility: visible;
        border-radius: 3px 3px 0 0;
    }

    .debugbar-resize-handle:hover {
        background: linear-gradient(90deg, hsl(var(--primary, 221.2 83.2% 53.3%)), hsl(var(--primary, 221.2 83.2% 53.3%) / 0.9));
    }

    .debugbar-resize-handle::after {
        content: '⋮⋮⋮';
        position: absolute;
        top: -12px;
        left: 50%;
        transform: translateX(-50%);
        color: hsl(var(--primary, 221.2 83.2% 53.3%));
        font-size: 14px;
        font-weight: 600;
        line-height: 1;
        pointer-events: none;
        transition: color 0.2s ease;
    }

    .debugbar-resize-handle:hover::after {
        color: hsl(var(--primary, 221.2 83.2% 53.3%));
    }

    /* Dark mode support */
    .dark #debugbar {
        --card: 222.2 84% 4.9%;
        --background: 222.2 84% 4.9%;
        --foreground: 210 40% 98%;
        --muted: 217.2 32.6% 17.5%;
        --muted-foreground: 215 20.2% 65.1%;
        --border: 217.2 32.6% 17.5%;
        --primary: 217.2 91.2% 59.8%;
    }
    body{
        padding-bottom: 50px;
    }
    #debugbar.collapsed {
        max-height: 56px;
        height: auto;
    }
    #debugbar:not(.collapsed) {
        max-height: calc(100vh - 60px);
    }
    #debugbar.collapsed .debugbar-content {
        display: none;
    }
    #debugbar.collapsed .debugbar-resize-handle {
        display: none;
    }
    #debugbar .icon-expanded {
        display: none;
    }
    #debugbar.collapsed .icon-collapsed {
        display: inline;
    }
    #debugbar:not(.collapsed) .icon-expanded {
        display: inline;
    }
    #debugbar:not(.collapsed) .icon-collapsed {
        display: none;
    }
    .debugbar-section.collapsed .debugbar-section-content {
        display: none;
    }
    .debugbar-section.collapsed .icon-expanded {
        display: none;
    }
    .debugbar-section:not(.collapsed) .icon-collapsed {
        display: none;
    }
    @media (max-width: 799px) {
        #debugbar {
            width: auto;
            left: auto;
            right: 1rem;
            bottom: 1rem;
            border-radius: 1rem;
            max-width: 100vw;
        }
        #debugbar.collapsed {
            width: auto;
            min-width: auto;
            max-height: none;
        }
        #debugbar.collapsed .debugbar-header {
            width: 3rem;
            height: 3rem;
            padding: 0;
            justify-content: center;
            background: hsl(var(--background, 0 0% 100%));
            border: 1px solid hsl(var(--border, 214.3 31.8% 91.4%));
        }
        #debugbar.collapsed .debugbar-title,
        #debugbar.collapsed .debugbar-stats {
            display: none;
        }
        #debugbar.collapsed .debugbar-toggle-btn {
            border: none;
            width: 100%;
            height: 100%;
            border-radius: inherit;
            background: transparent;
        }
        #debugbar:not(.collapsed) {
            right: 0;
            bottom: 0;
            left: 0;
            border-radius: 0;
            max-height: calc(100vh - 60px);
        }
    }
</style>

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
        
        <div style="max-height: 700px; overflow-y: auto;">
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

<script>
    function initDebugbarUI() {
        const debugbar = document.getElementById('debugbar');
        if (!debugbar) {
            return;
        }

        const SMALL_SCREEN_WIDTH = 800;

        function defaultExpandedHeight() {
            if (window.innerWidth < SMALL_SCREEN_WIDTH) {
                const target = Math.round(window.innerHeight * 0.6);
                return Math.min(Math.max(target, 260), 480);
            }
            return 400;
        }

        if (!debugbar.dataset.lastHeight) {
            debugbar.dataset.lastHeight = String(defaultExpandedHeight());
        }

        const header = document.getElementById('debugbar-header');
        const toggleBtn = document.getElementById('debugbar-toggle-btn');
        const tabs = debugbar.querySelectorAll('.debugbar-tab');
        const panels = debugbar.querySelectorAll('.debugbar-panel');
        const sections = debugbar.querySelectorAll('.debugbar-section[data-section]');

        function expandDebugbar() {
            const storedHeight = parseInt(debugbar.dataset.lastHeight || '400', 10);
            let targetHeight = Number.isNaN(storedHeight) ? defaultExpandedHeight() : storedHeight;
            if (window.innerWidth < SMALL_SCREEN_WIDTH) {
                targetHeight = Math.min(
                    Math.max(targetHeight, 260),
                    Math.round(window.innerHeight * 0.8)
                );
            }
            debugbar.classList.remove('collapsed');
            debugbar.style.height = targetHeight + 'px';
            debugbar.style.maxHeight = targetHeight + 'px';
            debugbar.dataset.lastHeight = targetHeight;
        }

        function collapseDebugbar() {
            if (!debugbar.classList.contains('collapsed')) {
                const currentHeight = debugbar.offsetHeight;
                if (currentHeight > 0) {
                    debugbar.dataset.lastHeight = currentHeight;
                }
            }
            debugbar.classList.add('collapsed');
            debugbar.style.height = '';
            debugbar.style.maxHeight = '';
        }

        function toggleDebugbar() {
            if (debugbar.classList.contains('collapsed')) {
                expandDebugbar();
            } else {
                collapseDebugbar();
            }
        }

        if (header) {
            header.addEventListener('click', function(event) {
                if (event.target.closest('#debugbar-toggle-btn')) {
                    return;
                }
                toggleDebugbar();
            });
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(event) {
                event.stopPropagation();
                toggleDebugbar();
            });
        }

        function activateTab(tabName) {
            if (!tabName) {
                return;
            }
            debugbar.dataset.activeTab = tabName;
            tabs.forEach(function(tabButton) {
                const isActive = tabButton.dataset.tab === tabName;
                tabButton.classList.toggle('active', isActive);
            });
            panels.forEach(function(panel) {
                const isActive = panel.dataset.panel === tabName;
                panel.classList.toggle('active', isActive);
            });
        }

        const initialTab = debugbar.dataset.activeTab || (tabs[0] ? tabs[0].dataset.tab : null);
        activateTab(initialTab);

        tabs.forEach(function(tabButton) {
            tabButton.addEventListener('click', function(event) {
                event.preventDefault();
                activateTab(tabButton.dataset.tab);
            });
        });

        sections.forEach(function(section) {
            const title = section.querySelector('.debugbar-section-title');
            if (!title) {
                return;
            }
            title.addEventListener('click', function() {
                section.classList.toggle('collapsed');
            });
        });

        collapseDebugbar();

        window.addEventListener('resize', function() {
            if (!debugbar.classList.contains('collapsed')) {
                debugbar.dataset.lastHeight = String(defaultExpandedHeight());
                expandDebugbar();
            } else {
                debugbar.dataset.lastHeight = String(defaultExpandedHeight());
            }
        });
    }

    // Copy to clipboard function
    function copyToClipboard(elementId, event) {
        try {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }

            const textarea = document.getElementById(elementId);
            if (!textarea) {
                console.error('Element not found:', elementId);
                return;
            }

            textarea.select();
            textarea.setSelectionRange(0, 99999);

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textarea.value).then(function() {
                    showCopySuccess(event.currentTarget);
                }).catch(function(err) {
                    fallbackCopy(textarea, event.currentTarget);
                });
            } else {
                fallbackCopy(textarea, event.currentTarget);
            }
        } catch (error) {
            console.error('Copy error:', error);
        }
    }

    function fallbackCopy(textarea, button) {
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                showCopySuccess(button);
            } else {
                console.error('Copy command failed');
            }
        } catch (err) {
            console.error('Fallback copy error:', err);
        }
    }

    function showCopySuccess(button) {
        if (!button) return;

        const originalHTML = button.innerHTML;
        button.classList.add('copied');
        button.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> Copied!';

        setTimeout(function() {
            button.classList.remove('copied');
            button.innerHTML = originalHTML;
        }, 2000);
    }

    // Resize functionality
    let isResizing = false;
    let startY = 0;
    let startHeight = 0;

    function initResize() {
        try {
            const resizeHandle = document.getElementById('debugbar-resize-handle');
            const debugbar = document.getElementById('debugbar');

            if (!resizeHandle || !debugbar) {
                return;
            }

            resizeHandle.addEventListener('mousedown', function(e) {
                try {
                    if (debugbar.classList.contains('collapsed')) {
                        return;
                    }
                    isResizing = true;
                    startY = e.clientY;
                    startHeight = parseInt(document.defaultView.getComputedStyle(debugbar).height, 10);

                    // Disable transition during resize for smooth dragging
                    debugbar.style.transition = 'none';

                    document.addEventListener('mousemove', handleResize);
                    document.addEventListener('mouseup', stopResize);

                    e.preventDefault();
                } catch (error) {
                    console.error('Debugbar resize mousedown error:', error);
                }
            });
        } catch (error) {
            console.error('Debugbar initResize error:', error);
        }
    }

    function handleResize(e) {
        try {
            if (!isResizing) return;

            const debugbar = document.getElementById('debugbar');
            if (!debugbar) return;
            if (debugbar.classList.contains('collapsed')) return;

            const newHeight = startHeight - (e.clientY - startY);
            const minHeight = 200;
            const maxHeight = Math.max(window.innerHeight - 100, 400);

            // Ensure height is within bounds
            const clampedHeight = Math.max(minHeight, Math.min(newHeight, maxHeight));
            debugbar.style.height = clampedHeight + 'px';
            debugbar.style.maxHeight = clampedHeight + 'px';
            debugbar.dataset.lastHeight = clampedHeight;
        } catch (error) {
            console.error('Debugbar handleResize error:', error);
        }
    }

    function stopResize() {
        try {
            if (!isResizing) return;

            isResizing = false;

            const debugbar = document.getElementById('debugbar');
            if (debugbar) {
                // Re-enable transition after resize
                debugbar.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                if (!debugbar.classList.contains('collapsed')) {
                    const currentHeight = parseInt(debugbar.style.height, 10);
                    if (!Number.isNaN(currentHeight)) {
                        debugbar.dataset.lastHeight = currentHeight;
                    }
                }
            }

            // Clean up event listeners
            document.removeEventListener('mousemove', handleResize);
            document.removeEventListener('mouseup', stopResize);
        } catch (error) {
            console.error('Debugbar stopResize error:', error);
        }
    }

    // Initialize resize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        try {
            initDebugbarUI();
            initResize();
        } catch (error) {
            console.error('Debugbar initialization error:', error);
        }
    });

    // Toggle hook details
    function toggleHookDetails(hookId) {
        try {
            const details = document.getElementById('hook-details-' + hookId);
            const icon = document.getElementById('hook-icon-' + hookId);
            
            if (!details) return;
            
            if (details.style.display === 'none' || details.style.display === '') {
                // Show details
                details.style.display = 'block';
                if (icon) {
                    icon.style.transform = 'rotate(90deg)';
                }
            } else {
                // Hide details
                details.style.display = 'none';
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            }
        } catch (error) {
            console.error('Toggle hook details error:', error);
        }
    }

    // Toggle profile children
    function toggleProfile(profileId) {
        try {
            const children = document.getElementById('profile-children-' + profileId);
            const icon = document.getElementById('profile-icon-' + profileId);
            
            if (!children) return;
            
            if (children.style.display === 'none' || children.style.display === '') {
                // Show children
                children.style.display = 'block';
                if (icon) {
                    icon.style.transform = 'rotate(0deg)';
                }
            } else {
                // Hide children
                children.style.display = 'none';
                if (icon) {
                    icon.style.transform = 'rotate(-90deg)';
                }
            }
        } catch (error) {
            console.error('Toggle profile error:', error);
        }
    }
</script>
