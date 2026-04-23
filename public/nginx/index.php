<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Full Form - Nginx Generator</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.prod.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-i18n@9.2.2/dist/vue-i18n.global.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="main.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body>

    <div id="app" class="min-h-screen">

        <!-- Header -->
        <header class="bg-white border-b shadow-sm">
            <div class="container mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex items-center" :class="isRTL ? 'space-x-reverse space-x-2' : 'space-x-2'">
                    <img src="https://cmsfullform.com/themes/cmsfullform/Frontend/Assets/images/logo/Logo.webp" alt="CMS Full Form" class="h-10" />
                </div>
                <div class="flex items-center space-x-2">
                    <button @click="generateNginxConfig" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ $t('header.generateConfig') }}
                    </button>

                    <!-- Language Selector -->
                    <div class="language-selector">
                        <div class="language-selector-btn" @click="toggleLanguageDropdown">
                            <img :src="getLanguageFlag(currentLanguage)" :alt="currentLanguage">
                            <span class="mx-1">{{ getLanguageName(currentLanguage) }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                        <div class="language-selector-dropdown" :class="{ 'show': showLanguageDropdown }">
                            <div
                                v-for="lang in availableLanguages"
                                :key="lang.code"
                                class="language-selector-item"
                                :class="{ 'active': currentLanguage === lang.code }"
                                @click="changeLanguage(lang.code)">
                                <img :src="lang.flag" :alt="lang.code">
                                <span>{{ lang.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container mx-auto px-4 py-6">
            <!-- Main Configuration Card -->
            <div class="card mb-6">
                <div class="tab-nav">
                    <div class="tab-item" :class="{ active: activeTab === 'basic' }" @click="activeTab = 'basic'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>{{ $t('tabs.basic') }}
                    </div>
                    <div class="tab-item" :class="{ active: activeTab === 'cache' }" @click="activeTab = 'cache'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>{{ $t('tabs.cache') }}
                    </div>
                    <div class="tab-item" :class="{ active: activeTab === 'api' }" @click="activeTab = 'api'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>{{ $t('tabs.paths') }}
                    </div>
                </div>

                <!-- Basic Settings Tab (including Compression) -->
                <div class="tab-content" :class="{ active: activeTab === 'basic' }">
                    <div class="card-body settings-container">
                        <!-- General Settings Card -->
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <svg class="w-5 h-5 settings-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <h3 class="settings-card-title">{{ $t('basicSettings.title') }}</h3>
                            </div>
                            <div class="settings-card-body">
                                <!-- Toggle Options: Debug + WebP on one row -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="toggle-item">
                                        <label class="toggle-label">
                                            <input type="checkbox" v-model="config.debug" class="toggle-checkbox">
                                            <div class="toggle-content">
                                                <span class="toggle-title">{{ $t('basicSettings.debugMode') }}</span>
                                                <span class="toggle-description">{{ $t('basicSettings.debugDescription') }}</span>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="toggle-item">
                                        <label class="toggle-label">
                                            <input type="checkbox" v-model="config.webpPriority" class="toggle-checkbox">
                                            <div class="toggle-content">
                                                <span class="toggle-title">{{ $t('basicSettings.webpPriority') }}</span>
                                                <span class="toggle-description">{{ $t('basicSettings.webpPriorityDescription') }}</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Path Configuration -->
                                <div class="settings-section-divider"></div>
                                <div class="settings-section-title">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                    </svg>
                                    Path Configuration
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="form-group-enhanced">
                                        <label class="form-label-enhanced">
                                            <svg class="w-4 h-4 form-label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            </svg>
                                            {{ $t('basicSettings.websiteRoot') }}
                                        </label>
                                        <?php
                                        // Normalize document root for placeholder and display
                                        $docRootPlaceholder = $_SERVER["DOCUMENT_ROOT"] ?? '';
                                        $docRootPlaceholder = rtrim($docRootPlaceholder, '/\\');
                                        if (preg_match('/\/public$/', $docRootPlaceholder)) {
                                            $docRootPlaceholder = substr($docRootPlaceholder, 0, -7);
                                        }
                                        ?>
                                        <input type="text" v-model="config.cacheRoot" class="form-control-enhanced"
                                            :placeholder="'<?= $docRootPlaceholder ?>'">
                                        <div class="form-hint">
                                            <span class="form-hint-text">{{ $t('basicSettings.current') }}: <code><?= $docRootPlaceholder ?></code></span>
                                            <span class="form-hint-link">{{ $t('basicSettings.websiteRootNote') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label class="form-label-enhanced">
                                            <svg class="w-4 h-4 form-label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            {{ $t('basicSettings.configFilename') }}
                                        </label>
                                        <input type="text" v-model="config.filename" class="form-control-enhanced" placeholder="cmsfullform.conf">
                                        <p class="form-hint-text">{{ $t('basicSettings.configFilenameNote') }}</p>
                                    </div>
                                </div>

                                <!-- Cache Expiration -->
                                <div class="settings-section-divider"></div>
                                <div class="settings-section-title">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Cache Expiration Settings
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="form-group-enhanced">
                                        <label class="form-label-enhanced">
                                            <svg class="w-4 h-4 form-label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                            </svg>
                                            {{ $t('basicSettings.cssExpiration') }}
                                        </label>
                                        <input type="text" v-model="config.cssExpiration" class="form-control-enhanced" placeholder="50d">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label class="form-label-enhanced">
                                            <svg class="w-4 h-4 form-label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                                            </svg>
                                            {{ $t('basicSettings.jsExpiration') }}
                                        </label>
                                        <input type="text" v-model="config.jsExpiration" class="form-control-enhanced" placeholder="50d">
                                    </div>
                                    <div class="form-group-enhanced">
                                        <label class="form-label-enhanced">
                                            <svg class="w-4 h-4 form-label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            {{ $t('basicSettings.mediaExpiration') }}
                                        </label>
                                        <input type="text" v-model="config.mediaExpiration" class="form-control-enhanced" placeholder="50d">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Compression Settings Card -->
                        <div class="settings-card">
                            <div class="settings-card-header">
                                <svg class="w-5 h-5 settings-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <h3 class="settings-card-title">{{ $t('basicSettings.compressionTitle') }}</h3>
                            </div>
                            <div class="settings-card-body">
                                <!-- Compression Toggles -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="toggle-item" :class="{ 'toggle-item-disabled': !gzipSupported }">
                                        <label class="toggle-label">
                                            <input type="checkbox" v-model="config.gzipEnabled" :disabled="!gzipSupported" class="toggle-checkbox">
                                            <div class="toggle-content">
                                                <span class="toggle-title">{{ $t('basicSettings.enableGzip') }}</span>
                                                <span class="toggle-description">{{ $t('basicSettings.enableGzipDescription') }}</span>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="toggle-item" :class="{ 'toggle-item-disabled': !brotliSupported }">
                                        <label class="toggle-label">
                                            <input type="checkbox" v-model="config.brotliEnabled" :disabled="!brotliSupported" class="toggle-checkbox">
                                            <div class="toggle-content">
                                                <span class="toggle-title">{{ $t('basicSettings.enableBrotli') }}</span>
                                                <span class="toggle-description">{{ $t('basicSettings.enableBrotliDescription') }}</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Compression Support Check -->
                                <div class="settings-section-divider"></div>
                                <div class="compression-support-card">
                                    <div class="compression-support-header">
                                        <div class="compression-support-title-group">
                                            <svg class="w-5 h-5 compression-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <h4 class="compression-support-title">{{ $t('basicSettings.serverSupport') }}</h4>
                                        </div>
                                        <button @click="checkCompressionSupport" class="btn btn-primary btn-sm compression-refresh-btn">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            {{ $t('basicSettings.refreshCheck') }}
                                        </button>
                                    </div>

                                    <div class="compression-status-grid">
                                        <!-- GZIP Support -->
                                        <div class="compression-status-item">
                                            <div class="compression-status-header">
                                                <span class="compression-status-label">{{ $t('basicSettings.gzipStatus') }}</span>
                                                <span id="gzip-status" class="status-badge status-info">{{ $t('status.checking') }}</span>
                                            </div>
                                            <div id="gzip-details" class="compression-status-details">
                                                <p>{{ $t('basicSettings.gzipDetails') }}</p>
                                            </div>
                                        </div>

                                        <!-- Brotli Support -->
                                        <div class="compression-status-item">
                                            <div class="compression-status-header">
                                                <span class="compression-status-label">{{ $t('basicSettings.brotliStatus') }}</span>
                                                <span id="brotli-status" class="status-badge status-info">{{ $t('status.checking') }}</span>
                                            </div>
                                            <div id="brotli-details" class="compression-status-details">
                                                <p>{{ $t('basicSettings.brotliDetails') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="config.brotliEnabled" class="info-alert">
                                    <svg class="w-5 h-5 info-alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="info-alert-content">
                                        <strong>{{ $t('common.note') }}:</strong> {{ $t('basicSettings.brotliNote') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cache Rules Tab -->
                <div class="tab-content" :class="{ active: activeTab === 'cache' }">
                    <div class="card-body">
                        <!-- URI Patterns: single list, each row = Pattern | Description | =/~/~* | Allow/Block Cache -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">{{ $t('cacheRules.title') }}</h3>
                            <p class="text-sm text-gray-600 mb-4">{{ $t('cacheRules.unifiedHint') }}</p>

                            <!-- Add URI Rule Form -->
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <h4 class="text-sm font-medium mb-3 text-gray-700">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ $t('cacheRules.addUriRule') }}
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 mb-3 items-end">
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('cacheRules.uriPattern') }}</label>
                                        <input type="text" v-model="newUriRule.path" class="form-control"
                                            :placeholder="$t('cacheRules.uriPattern')">
                                    </div>
                                    <div class="md:col-span-3">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('cacheRules.description') }}</label>
                                        <input type="text" v-model="newUriRule.description" class="form-control"
                                            :placeholder="$t('cacheRules.description')">
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('cacheRules.condition') }}</label>
                                        <select v-model="newUriRule.condition" class="form-control">
                                            <option value="=">{{ $t('cacheRules.exact') }}</option>
                                            <option value="~">{{ $t('cacheRules.regex') }}</option>
                                            <option value="~*">{{ $t('cacheRules.iregex') }}</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-medium text-gray-600 mb-1">{{ $t('cacheRules.cacheAction') }}</label>
                                        <select v-model="newUriRule.exclude" class="form-control">
                                            <option :value="false">{{ $t('cacheRules.allowCache') }}</option>
                                            <option :value="true">{{ $t('cacheRules.blockCache') }}</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <button @click="addUriRule" class="btn btn-primary w-full">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            {{ $t('common.add') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- URI Patterns table -->
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">{{ $t('cacheRules.uriPattern') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">{{ $t('cacheRules.description') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">{{ $t('cacheRules.condition') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-600 uppercase">{{ $t('cacheRules.cacheAction') }}</th>
                                            <th class="px-4 py-3 w-12"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(rule, index) in config.cacheableUris" :key="index"
                                            :class="rule.exclude ? 'bg-red-100' : 'bg-green-100'">
                                            <td class="px-4 py-3 text-sm" :class="rule.exclude ? 'text-red-800' : 'text-green-800'">
                                                <span v-if="rule.exclude" class="line-through">{{ rule.path }}</span>
                                                <span v-else>{{ rule.path }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ rule.description || '—' }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 rounded text-xs font-medium" :class="rule.exclude ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                                                    {{ rule.condition }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <select v-model="rule.exclude" class="form-control text-sm py-1 max-w-[10rem]" :class="rule.exclude ? 'border-red-300' : 'border-green-300'">
                                                    <option :value="false">{{ $t('cacheRules.allowCache') }}</option>
                                                    <option :value="true">{{ $t('cacheRules.blockCache') }}</option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-3">
                                                <button @click="removeUriRule(index)" class="text-red-500 hover:text-red-700" :title="$t('common.delete')">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="config.cacheableUris.length === 0">
                                            <td colspan="5" class="px-4 py-8 text-center text-gray-500 text-sm">
                                                {{ $t('cacheRules.noUriRules') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Query Parameters -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold mb-4">{{ $t('cacheRules.queryParamsTitle') }}</h3>
                            <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                                    <input type="text" v-model="newQueryParam.key" class="form-control"
                                        :placeholder="$t('cacheRules.parameterKey')">
                                    <button @click="addQueryParam" class="btn btn-success">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>{{ $t('cacheRules.addParameter') }}
                                    </button>
                                </div>
                            </div>

                            <!-- 8 items per row -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-3">
                                <div v-for="(param, index) in config.cacheQueryParams" :key="index"
                                    class="bg-blue-50 border border-blue-200 p-3 rounded-lg flex flex-col items-center justify-between text-center">
                                    <div class="w-full">
                                        <span class="font-medium text-blue-800 block mb-1">{{param.key}}</span>
                                    </div>
                                    <button @click="removeQueryParam(index)" class="text-blue-500 hover:text-blue-700 mt-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cookies & Mobile Agents - Full width -->
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold mb-4">{{ $t('cacheRules.cookiesTitle') }}</h3>
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <div class="flex space-x-2">
                                        <input type="text" v-model="newCookie" class="form-control flex-1"
                                            :placeholder="$t('cacheRules.cookieName')">
                                        <button @click="addCookie" class="btn btn-primary">{{ $t('common.add') }}</button>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="(cookie, index) in config.cookieInvalidate" :key="index"
                                        class="bg-red-100 text-red-800 rounded-full px-3 py-1 flex items-center">
                                        <span>{{ cookie }}</span>
                                        <button @click="removeCookie(index)" class="ml-2 text-red-600 hover:text-red-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold mb-4">{{ $t('cacheRules.mobileAgentsTitle') }}</h3>
                                <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                    <div class="flex space-x-2">
                                        <input type="text" v-model="newMobileAgent" class="form-control flex-1"
                                            :placeholder="$t('cacheRules.userAgentPattern')">
                                        <button @click="addMobileAgent" class="btn btn-primary">{{ $t('common.add') }}</button>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="(agent, index) in config.mobileUserAgents" :key="index"
                                        class="bg-green-100 text-green-800 rounded-full px-3 py-1 flex items-center">
                                        <span>{{ agent }}</span>
                                        <button @click="removeMobileAgent(index)" class="ml-2 text-green-600 hover:text-green-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API & Paths Tab -->
                <div class="tab-content" :class="{ active: activeTab === 'api' }">
                    <div class="card-body">
                        <!-- API Configuration -->
                        <p class="mb-2 bg-red-200 rounded-lg p-2">
                            <strong>{{ $t('common.note') }}:</strong> {{ $t('pathsConfig.note') }}
                        </p>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4">{{ $t('pathsConfig.apiTitle') }}</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="form-group">
                                    <label class="form-label">{{ $t('pathsConfig.enableApi') }}</label>
                                    <label class="flex items-center">
                                        <input type="checkbox" v-model="config.apiEnabled" class="mr-2">
                                        <span>{{ $t('pathsConfig.enableApiDescription') }}</span>
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ $t('pathsConfig.apiUriPath') }}</label>
                                    <input type="text" v-model="config.apiPath" class="form-control" placeholder="^/api/">
                                    <p class="text-xs text-gray-500 mt-1">Regex pattern to match API routes (e.g., ^/api/, ^.*/api/, ^/v1/api/)</p>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ $t('pathsConfig.apiExpiration') }}</label>
                                    <input type="text" v-model="config.apiExpiration" class="form-control" placeholder="1h">
                                </div>
                            </div>
                        </div>

                        <!-- Content (combined: themes, plugins, uploads) -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="font-semibold">Content Path Configuration</h4>
                            </div>
                            <div class="card-body">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="form-group">
                                        <label class="form-label">Content Path</label>
                                        <input type="text" v-model="config.contentPath" class="form-control" placeholder="/content">
                                        <p class="text-xs text-gray-500 mt-1">URL path for content files (themes, plugins, uploads)</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Content Alias</label>
                                        <input type="text" v-model="config.contentAlias" class="form-control" placeholder="/content">
                                        <p class="text-xs text-gray-500 mt-1">File system path alias (relative to cache root)</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Content Expiration</label>
                                        <input type="text" v-model="config.contentExpiration" class="form-control" placeholder="365d">
                                        <p class="text-xs text-gray-500 mt-1">Cache expiration time (e.g., 365d, 30d, 1h)</p>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Allowed Extensions</label>
                                    <textarea v-model="config.contentAllowedExtensions" class="form-control" rows="3"
                                        placeholder="css,js,map,wasm,ico,woff,woff2,ttf,eot,otf,svg,webp,avif,jpg,jpeg,png,gif,bmp,tiff,pdf,txt,json,xml,yaml,yml,docx,doc,xls,xlsx,csv,ppt,pptx,rtf,odt,ods,odp,rar,zip,7z,tar,gz,bz2,iso,mp3,mp4,wav,ogg,webm,m4a,flac,mkv,srt"></textarea>
                                    <p class="text-xs text-red-600 mt-1">Comma-separated list of allowed file extensions. Only these extensions will be served. You can enter multiple lines for better readability.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Generate Config button at bottom of card -->
                <div class="border-t border-gray-200 px-4 py-4 flex justify-end bg-gray-50 rounded-b-lg">
                    <button @click="generateNginxConfig" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        {{ $t('header.generateConfig') }}
                    </button>
                </div>
            </div>

            <!-- Output Section -->
            <div v-if="config.generatedNginx" class="card">
                <div class="card-header">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-green-800">
                            <svg class="w-6 h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>{{ $t('output.title') }}
                        </h3>
                        <button @click="copyToClipboard()" class="btn btn-secondary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>{{ $t('output.copyInstructions') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-green-800">
                                    {{ $t('output.successMessage') }}
                                </h3>
                                <div class="mt-2 text-sm text-green-700">
                                    <p>{{ $t('output.successDescription') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4" id="nginx-instructions">
                        <h3 class="text-lg font-semibold mb-4 text-blue-800">{{ $t('output.instructionsTitle') }}</h3>
                        <p class="text-blue-700 mb-4">{{ $t('output.instructionsDescription') }}<br />
                            Path Maybe: <b>/etc/nginx/conf.d/<?= explode(':', $_SERVER['HTTP_HOST'])[0] ?>.conf </b></p>

                        <div class="bg-white border rounded-lg p-4 font-mono text-sm">
                            <div class="text-gray-600 mb-2"># Add /public into root path nginx.</div>
                            <div class="text-gray-600 mb-2">root {{config.cacheRoot}}/public;</div>
                            <div class="text-gray-600 mb-2"></div>
                            <div class="text-gray-600 mb-2">#################### CMSFULLFORM CONFIG START ####################</div>
                            <div class="text-gray-600 mb-2"># Step 1: Caching Nginx Config</div>
                            <div class="text-gray-600 mb-2">include "{{config.cacheRoot}}/{{config.filename || 'cmsfullform.conf'}}";</div>
                            <div class="text-gray-600 mb-2"># Step 2: Rewrite All Request to PHP CMS Index</div>
                            <div class="text-gray-600 mb-2">location / { try_files $uri $uri/ /index.php$is_args$args; }</div>

                            <div v-if="config.webpPriority">
                                <div class="text-gray-600 mb-2"># Step 3: WebP Rewrite (no map)</div>
                                <div class="text-gray-600 mb-2"># ----------------------------------------------------------</div>
                                <div class="text-gray-600 mb-2">location ~* ^.+\.(png|jpe?g|gif)$ {</div>
                                <div class="text-gray-600 mb-2"> add_header Vary Accept;</div>
                                <div class="text-gray-600 mb-2"> gzip_static off;</div>
                                <div class="text-gray-600 mb-2"> {{ config.brotliEnabled ? 'brotli_static off;' : '#brotli_static off;' }}</div>
                                <div class="text-gray-600 mb-2"> #add_header Access-Control-Allow-Origin *;</div>
                                <div class="text-gray-600 mb-2"> add_header Cache-Control "public, must-revalidate, proxy-revalidate, immutable, stale-while-revalidate=86400, stale-if-error=604800";</div>
                                <div class="text-gray-600 mb-2"> access_log off;</div>
                                <div class="text-gray-600 mb-2"> expires 365d;</div>
                                <div class="text-gray-600 mb-2"> try_files $uri$webp_suffix $uri =404;</div>
                                <div class="text-gray-600 mb-2">}</div>
                                <div class="text-gray-600 mb-2"></div>
                                <div class="text-gray-600 mb-2"># ----------------------------------------------------------</div>
                            </div>
                            <div class="text-gray-600 mb-2">#################### CMSFULLFORM CONFIG END ####################</div>
                        </div>

                        <div class="mt-4 text-sm text-blue-600">
                            <p><strong>Note:</strong> The paths above use your configured cache root: <code>{{config.cacheRoot}}</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        <?php
        // Normalize document root: remove /public or /public/ from the end
        $docRoot = $_SERVER["DOCUMENT_ROOT"] ?? '';
        $docRoot = rtrim($docRoot, '/\\');
        if (preg_match('/\/public$/', $docRoot)) {
            $docRoot = substr($docRoot, 0, -7); // Remove '/public' (7 chars)
        }
        ?>
        var documentRoot = "<?= $docRoot ?>";
    </script>

    <script type="text/javascript" src="./locales/en.js"></script>
    <script type="text/javascript" src="./locales/vi.js"></script>
    <script type="text/javascript" src="./locales/fr.js"></script>
    <script type="text/javascript" src="./locales/zh.js"></script>
    <script type="text/javascript" src="./locales/es.js"></script>
    <script type="text/javascript" src="./locales/ar.js"></script>
    <script type="text/javascript" src="./locales/pt.js"></script>
    <script type="text/javascript" src="./locales/ru.js"></script>
    <script type="text/javascript" src="./locales/de.js"></script>
    <script type="text/javascript" src="./locales/ja.js"></script>
    <script type="text/javascript" src="./locales/hi.js"></script>
    <script type="text/javascript" src="./locales/ko.js"></script>
    <script type="text/javascript" src="./locales/th.js"></script>
    <script type="text/javascript" src="./main.js"></script>
    <script>
        // Initialize Feather icons
        feather.replace();
    </script>

    <style>
        .language-selector {
            position: relative;
            display: inline-block;
        }

        .language-selector-btn {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            transition: all 0.2s;
        }

        .language-selector-btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .language-selector-btn img {
            width: 20px;
            height: 15px;
            border-radius: 2px;
        }

        .language-selector-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 4px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            min-width: 200px;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s;
        }

        .language-selector-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .language-selector-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .language-selector-item:hover {
            background: #f9fafb;
        }

        .language-selector-item.active {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .language-selector-item img {
            width: 20px;
            height: 15px;
            border-radius: 2px;
            margin-right: 12px;
        }

        .language-selector-item span {
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</body>

</html>