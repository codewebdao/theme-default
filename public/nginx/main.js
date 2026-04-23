

// Constants and initial state
const { createApp, ref, reactive, computed } = Vue;
const { createI18n } = VueI18n;

// Create i18n instance
const i18n = createI18n({
    locale: 'en',
    fallbackLocale: 'en',
    messages: {
        en: enMessages,
        vi: viMessages,
        fr: frMessages,
        zh: zhMessages,
        es: esMessages,
        ar: arMessages,
        pt: ptMessages,
        ru: ruMessages,
        de: deMessages,
        ja: jaMessages,
        hi: hiMessages,
        ko: koMessages,
        th: thMessages
    }
});

const DEFAULT_CONFIG = {
   debug: false,
   cacheRoot: documentRoot,
   pageCacheExpiration: '1h',
   browserCacheExpiration: '1d',
   queryParamCacheExpiration: '1h',
   cookieInvalidate: [
       'cmsff_logged',
       'wp\\-postpass_'
   ],
   mobileUserAgents: [
    'phone',
    'windows\\s+phone', 
    'ipod',
    'ipad',
    'blackberry',
    '(?:android|bb\\d+|meego|silk|googlebot) .+? mobile',
    'palm',
    'windows\\s+ce',
    'opera mini',
    'avantgo', 
    'mobilesafari',
    'docomo',
    'kaios'
   ],
   cacheableUris: [
       {path: "/", description: "Homepage", condition: '=', exclude: false},
       {path: "^/home/index/", description: "Categories path", condition: '~', exclude: false},
       {path: "^.*/api/", description: "API paths", condition: '~', exclude: false},
       {path: "^.*/api/v1/auth/", description: "Auth paths", condition: '~', exclude: true},
       {path: "^/account/", description: "Account paths", condition: '~', exclude: true}
   ],
   cacheQueryParams: [
       {key: "id"},
       {key: "page"},
       {key: "limit"},
       {key: "sort"},
       {key: "order"},
       {key: "paged"},
       {key: "sortby"},
       {key: "orderby"}
   ],
   mediaExtensions: 'ico|gif|jpe?g|png|svg|eot|otf|woff|woff2|ttf|ogg|webp',
   cssExpiration: '50d',
   jsExpiration: '50d', 
   mediaExpiration: '50d',
   // New configuration options
   apiEnabled: true,
   apiPath: '/api/',
   apiExpiration: '1h',
   contentPath: '/content',
   contentAlias: '/content',
   contentExpiration: '365d',
   contentAllowedExtensions: 'css,js,map,wasm,ico,cur,woff,woff2,ttf,eot,otf,svg,webp,avif,jpg,jpeg,png,gif,bmp,tiff,pdf,txt,json,xml,yaml,yml,docx,doc,xls,xlsx,csv,ppt,pptx,rtf,odt,ods,odp,rar,zip,7z,tar,gz,bz2,iso,mp3,mp4,m4v,wav,ogg,webm,m4a,flac,mkv,srt',
   gzipEnabled: true,
   brotliEnabled: false,
   webpPriority: true,
   filename: 'cmsfullform.conf',
   generatedINI: '',
   generatedNginx: ''
};

const app = createApp({
   setup() {
       const config = reactive({...DEFAULT_CONFIG});
       // Ensure every URI rule has explicit exclude (Allow/Block) so dropdown never shows blank
       if (Array.isArray(config.cacheableUris)) {
           config.cacheableUris.forEach(r => {
               if (r.exclude !== true && r.exclude !== false) r.exclude = false;
           });
       }
       const activeTab = ref('basic');
       const newCookie = ref('');
       const newMobileAgent = ref('');
       const newUriRule = ref({
           path: '',
           description: '',
           condition: '=',
           exclude: false   // false = Allow Cache, true = Block Cache
       });
       const newQueryParam = ref({
           key: ''
       });
       // Compression support flags
       const gzipSupported = ref(true); // Default to true, will be updated by check
       const brotliSupported = ref(true); // Default to true, will be updated by check

       // Management methods
       const addCookie = () => {
           if (newCookie.value && !config.cookieInvalidate.includes(newCookie.value)) {
               config.cookieInvalidate.push(newCookie.value);
               newCookie.value = '';
           }
       };

       const removeCookie = (index) => {
           config.cookieInvalidate.splice(index, 1);
       };

       const addMobileAgent = () => {
           if (newMobileAgent.value && !config.mobileUserAgents.includes(newMobileAgent.value)) {
               config.mobileUserAgents.push(newMobileAgent.value);
               newMobileAgent.value = '';
           }
       };

       const removeMobileAgent = (index) => {
           config.mobileUserAgents.splice(index, 1);
       };

       const addUriRule = () => {
           if (!newUriRule.value.path) return;
           const ruleToAdd = {
               path: newUriRule.value.path,
               description: newUriRule.value.description || '',
               condition: newUriRule.value.condition || '=',
               exclude: !!newUriRule.value.exclude
           };
           if (!config.cacheableUris.some(r => r.path === ruleToAdd.path && r.exclude === ruleToAdd.exclude)) {
               config.cacheableUris.push(ruleToAdd);
               newUriRule.value = { path: '', description: '', condition: '=', exclude: false };
           }
       };

       const removeUriRule = (index) => {
           config.cacheableUris.splice(index, 1);
       };

       const addQueryParam = () => {
           if (newQueryParam.value.key && !config.cacheQueryParams.some(param => param.key === newQueryParam.value.key)) {
               config.cacheQueryParams.push({key: newQueryParam.value.key});
               newQueryParam.value = {key: ''};
           }
       };

       const removeQueryParam = (index) => {
           config.cacheQueryParams.splice(index, 1);
       };

        // Generate Nginx configuration  
        const generateNginxConfig = async () => {
            // Scroll to instructions section immediately when button is clicked
            setTimeout(() => {
                const instructionsSection = document.getElementById('nginx-instructions');
                if (instructionsSection) {
                    instructionsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
            
            try {
                // Normalize config for API: preserve cacheableUris order, ensure types (exclude = boolean)
                const payload = { ...config };
                if (Array.isArray(payload.cacheableUris)) {
                    payload.cacheableUris = payload.cacheableUris.map(r => ({
                        path: String(r.path || '').trim(),
                        description: String(r.description || ''),
                        condition: ['=', '~', '~*'].includes(r.condition) ? r.condition : '=',
                        exclude: r.exclude === true || r.exclude === 'true'
                    })).filter(r => r.path !== '');
                }
                const formData = new FormData();
                formData.append('action', 'generate_config');
                formData.append('config', JSON.stringify(payload));
                
                const response = await fetch('api/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success && result.config) {
                    // Save generated config to server
                    const saveResult = await saveNginxConfig(result.config);
                    
                    if (saveResult.success) {
                        config.generatedNginx = result.config;
                        
                        // Update success message with file path
                        const successMessage = document.querySelector('.bg-green-50 .text-green-700 p');
                        if (successMessage) {
                            successMessage.innerHTML = `The configuration file '${config.filename || 'cmsfullform.conf'}' has been generated and saved to: <code class="bg-green-100 px-2 py-1 rounded">${saveResult.filePath}</code>`;
                        }
                    } else {
                        config.generatedNginx = result.config;
                        
                        // Show error in the UI
                        const successMessage = document.querySelector('.bg-green-50 .text-green-700 p');
                        if (successMessage) {
                            successMessage.innerHTML = `Configuration was generated but failed to save: <span class="text-red-600">${saveResult.error}</span>`;
                        }
                    }
                } else {
                    alert('Error: ' + (result.error || 'Failed to generate configuration'));
                }
            } catch (error) {
                console.error('Error generating config:', error);
                alert('Error generating configuration: ' + error.message);
            }
        };

        // Save nginx configuration to server
        const saveNginxConfig = async (nginxConfig) => {
            try {
                const formData = new FormData();
                formData.append('action', 'save_config');
                formData.append('config', nginxConfig);
                formData.append('cacheRoot', config.cacheRoot || documentRoot);
                formData.append('filename', config.filename || 'cmsfullform.conf');
                
                const response = await fetch('api/api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    return {
                        success: true,
                        filePath: result.file_path || result.filePath || 'Unknown'
                    };
                } else {
                    return {
                        success: false,
                        error: result.error || 'Unknown error'
                    };
                }
            } catch (error) {
                console.error('Error saving nginx config:', error);
                return {
                    success: false,
                    error: error.message || 'Unknown error'
                };
            }
        };

        // Sanitize allowed extensions - remove dangerous executable files
        const sanitizeExtensions = (extensions) => {
            // Dangerous extensions to block
            const dangerousExtensions = [
                'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
                'phps', 'phpt', 'pht', 'phtm', 'phpt', 'pgif', 'shtml',
                'htaccess', 'htpasswd', 'ini', 'conf', 'config',
                'sh', 'bash', 'cgi', 'pl', 'py', 'rb', 'exe', 'dll', 'so',
                'asp', 'aspx', 'jsp', 'jspx', 'cfm', 'cfc'
            ];
            
            // Convert string to array
            let extArray = [];
            if (typeof extensions === 'string') {
                extArray = extensions.split(',').map(e => e.trim().toLowerCase());
            } else if (Array.isArray(extensions)) {
                extArray = extensions.map(e => String(e).trim().toLowerCase());
            }
            
            // Filter out dangerous extensions
            const safeExtensions = extArray.filter(ext => {
                ext = ext.replace(/^\./, ''); // Remove leading dot
                return ext && !dangerousExtensions.includes(ext);
            });
            
            // Remove duplicates
            return [...new Set(safeExtensions)];
        };

        // Clipboard helper
        const copyToClipboard = async (text) => {
            let instructions = `# Add /public into root path nginx.
root ${config.cacheRoot}/public;

#################### CMSFULLFORM CONFIG START ####################
# Step 1: Caching Nginx Config
include "${config.cacheRoot}/${config.filename || 'cmsfullform.conf'}";
# Step 2: Rewrite All Request to PHP CMS Index
location / { try_files $uri $uri/ /index.php$is_args$args; }`;
            
            if (config.webpPriority) {
                instructions += `
# Step 3: WebP Rewrite (no map)
# ----------------------------------------------------------
location ~* ^.+\.(png|jpe?g|gif)$ {
	add_header Vary Accept;
	gzip_static off;
	${config.brotliEnabled ? 'brotli_static off;' : '#brotli_static off;'}
	#add_header Access-Control-Allow-Origin *;
	add_header Cache-Control "public, must-revalidate, proxy-revalidate, immutable, stale-while-revalidate=86400, stale-if-error=604800";
	access_log off;
	expires 365d;
	try_files $uri$webp_suffix $uri =404;
}
# ----------------------------------------------------------`;
            }
            
            instructions += `
#################### CMSFULLFORM CONFIG END ####################`;
            
            try {
                await navigator.clipboard.writeText(instructions);
                alert('Instructions copied to clipboard!');
            } catch (err) {
                alert('Failed to copy to clipboard');
                console.error(err);
            }
        };

        // Compression support checker
        const checkCompressionSupport = async () => {
            const gzipStatusElement = document.getElementById('gzip-status');
            const gzipDetailsElement = document.getElementById('gzip-details');
            const brotliStatusElement = document.getElementById('brotli-status');
            const brotliDetailsElement = document.getElementById('brotli-details');
            
            // Set initial status
            gzipStatusElement.textContent = 'Checking...';
            gzipStatusElement.className = 'ml-2 px-2 py-1 rounded text-sm font-medium bg-yellow-100 text-yellow-800';
            brotliStatusElement.textContent = 'Checking...';
            brotliStatusElement.className = 'ml-2 px-2 py-1 rounded text-sm font-medium bg-yellow-100 text-yellow-800';
            
            try {
                const response = await fetch('api/api.php?action=check_compression');
                const result = await response.json();
                
                // Handle GZIP support
                if (result.gzip) {
                    if (result.gzip.supported) {
                    gzipSupported.value = true;
                    gzipStatusElement.textContent = 'Supported ✓';
                    gzipStatusElement.className = 'ml-2 px-2 py-1 rounded text-sm font-medium bg-green-100 text-green-800';
                    gzipDetailsElement.innerHTML = `
                        <p class="text-green-700"><strong>Great!</strong> Your server supports GZIP compression.</p>
                        <p class="text-sm mt-1">PHP GZIP extension: Available</p>
                        <p class="text-sm">GZIP compression has been automatically enabled.</p>
                    `;
                    // Auto-enable GZIP
                    config.gzipEnabled = true;
                } else {
                    gzipSupported.value = false;
                    gzipStatusElement.textContent = 'Not Supported ✗';
                    gzipStatusElement.className = 'ml-2 px-2 py-1 rounded text-sm font-medium bg-red-100 text-red-800';
                    gzipDetailsElement.innerHTML = `
                        <p class="text-red-700"><strong>Warning:</strong> Your server does not support GZIP compression.</p>
                        <p class="text-sm mt-1">GZIP compression has been automatically disabled.</p>
                        <p class="text-sm mt-1">To enable GZIP, you need to:</p>
                        <ul class="text-sm mt-1 ml-4 list-disc">
                            <li>Check PHP configuration</li>
                            <li>Ensure zlib extension is enabled</li>
                        </ul>
                    `;
                        // Auto-disable GZIP if not supported
                        config.gzipEnabled = false;
                    }
                } else {
                    // No GZIP info in response, assume not supported
                    gzipSupported.value = false;
                    config.gzipEnabled = false;
                    gzipStatusElement.textContent = 'Not Supported ✗';
                    gzipStatusElement.className = 'ml-2 px-2 py-1 rounded text-sm font-medium bg-red-100 text-red-800';
                    gzipDetailsElement.innerHTML = `
                        <p class="text-red-700"><strong>Warning:</strong> Could not determine GZIP support.</p>
                        <p class="text-sm mt-1">GZIP compression has been automatically disabled.</p>
                    `;
                }
                
                // Handle Brotli support with detailed info and recommendations
                if (result.brotli) {
                    const brotli = result.brotli;
                    const details = brotli.details || {};
                    const info = brotli.info || [];
                    const recommendations = brotli.recommendations || [];
                    
                    if (brotli.supported) {
                        brotliSupported.value = true;
                        brotliStatusElement.textContent = 'Supported ✓';
                        brotliStatusElement.className = 'ml-2 px-2 py-1 rounded text-sm font-medium bg-green-100 text-green-800';
                        
                        let html = `<p class="text-green-700"><strong>Great!</strong> Your server supports Brotli compression.</p>`;
                        html += `<p class="text-sm mt-1"><strong>PHP Brotli:</strong> ${details.extension_loaded ? '✓ Installed' : '✗ Not installed'}</p>`;
                        html += `<p class="text-sm"><strong>Nginx Brotli:</strong> ${details.nginx_brotli ? '✓ Active' : '✗ Not active'}</p>`;
                        html += `<p class="text-sm mt-2">You can manually enable Brotli compression above if desired.</p>`;
                        
                        brotliDetailsElement.innerHTML = html;
                    } else {
                        brotliSupported.value = false;
                        brotliStatusElement.textContent = 'Not Supported ✗';
                        brotliStatusElement.className = 'ml-2 px-2 py-1 rounded text-sm font-medium bg-red-100 text-red-800';
                        // Auto-disable Brotli if not supported
                        config.brotliEnabled = false;
                        
                        let html = `<p class="text-red-700"><strong>Info:</strong> Your server does not fully support Brotli compression.</p>`;
                        
                        // Show status details
                        html += `<div class="mt-2 text-sm">`;
                        html += `<p><strong>PHP Brotli Extension:</strong> ${details.extension_loaded ? '✓ Installed' : '✗ Not installed'}</p>`;
                        html += `<p><strong>Nginx Brotli Module:</strong> ${details.nginx_brotli ? '✓ Active' : '✗ Not active'}</p>`;
                        html += `</div>`;
                        
                        if (info.length > 0 || recommendations.length > 0) {
                            const showDetailsLabel = i18n.global.t('basicSettings.showDetails');
                            const hideDetailsLabel = i18n.global.t('basicSettings.hideDetails');
                            html += `<div class="mt-2">`;
                            html += `<button type="button" class="brotli-details-toggle text-sm text-blue-600 hover:text-blue-800 underline focus:outline-none" onclick="var el=document.getElementById('brotli-details-extra');var btn=this;if(el.style.display==='none'||!el.style.display){el.style.display='block';btn.textContent=btn.dataset.labelHide;}else{el.style.display='none';btn.textContent=btn.dataset.labelShow;}" data-label-show="${showDetailsLabel.replace(/"/g, '&quot;')}" data-label-hide="${hideDetailsLabel.replace(/"/g, '&quot;')}">${showDetailsLabel}</button>`;
                            html += `<div id="brotli-details-extra" class="mt-2" style="display:none">`;
                            if (info.length > 0) {
                                html += `<div class="mt-3 p-2 bg-yellow-50 border border-yellow-200 rounded">`;
                                html += `<p class="text-sm font-medium text-yellow-800 mb-1">Diagnostic Information:</p>`;
                                html += `<ul class="text-sm text-yellow-700 ml-4 list-disc">`;
                                info.forEach(msg => {
                                    html += `<li>${msg}</li>`;
                                });
                                html += `</ul>`;
                                html += `</div>`;
                            }
                            if (recommendations.length > 0) {
                                html += `<div class="mt-3 p-2 bg-blue-50 border border-blue-200 rounded">`;
                                html += `<p class="text-sm font-medium text-blue-800 mb-1">Recommendations:</p>`;
                                html += `<ul class="text-sm text-blue-700 ml-4 list-disc">`;
                                recommendations.forEach(rec => {
                                    html += `<li>${rec}</li>`;
                                });
                                html += `</ul>`;
                                html += `</div>`;
                            }
                            html += `</div></div>`;
                        }
                        
                        brotliDetailsElement.innerHTML = html;
                    }
                } else {
                    // No Brotli info in response, assume not supported
                    brotliSupported.value = false;
                    config.brotliEnabled = false;
                    brotliStatusElement.textContent = 'Not Supported ✗';
                    brotliStatusElement.className = 'ml-2 px-2 py-1 rounded text-sm font-medium bg-red-100 text-red-800';
                    brotliDetailsElement.innerHTML = `
                        <p class="text-red-700"><strong>Info:</strong> Could not determine Brotli support.</p>
                        <p class="text-sm mt-1">Brotli compression has been automatically disabled.</p>
                    `;
                }
            } catch (error) {
                // On error, assume not supported and disable
                gzipSupported.value = false;
                brotliSupported.value = false;
                config.gzipEnabled = false;
                config.brotliEnabled = false;
                
                gzipStatusElement.textContent = 'Check Failed ✗';
                gzipStatusElement.className = 'ml-2 px-2 py-1 rounded text-sm font-medium bg-red-100 text-red-800';
                brotliStatusElement.textContent = 'Check Failed ✗';
                brotliStatusElement.className = 'ml-2 px-2 px-2 py-1 rounded text-sm font-medium bg-red-100 text-red-800';
                
                gzipDetailsElement.innerHTML = `
                    <p class="text-red-700"><strong>Error:</strong> Could not check compression support.</p>
                    <p class="text-sm mt-1">Please check your server configuration or try again later.</p>
                `;
                brotliDetailsElement.innerHTML = `
                    <p class="text-red-700"><strong>Error:</strong> Could not check compression support.</p>
                    <p class="text-sm mt-1">Please check your server configuration or try again later.</p>
                `;
                console.error('Compression check failed:', error);
            }
        };

        // Auto-check compression support when component mounts
        // Using setTimeout to ensure DOM is ready
        setTimeout(() => {
            checkCompressionSupport();
        }, 100);

        // Language selector functionality
        const currentLanguage = ref('en');
        const showLanguageDropdown = ref(false);
        
        const availableLanguages = [
            { code: 'en', name: 'English', flag: 'https://flagcdn.com/w20/us.png' },
            { code: 'zh', name: '中文', flag: 'https://flagcdn.com/w20/cn.png' },
            { code: 'es', name: 'Español', flag: 'https://flagcdn.com/w20/es.png' },
            { code: 'ar', name: 'العربية', flag: 'https://flagcdn.com/w20/sa.png' },
            { code: 'pt', name: 'Português', flag: 'https://flagcdn.com/w20/br.png' },
            { code: 'ru', name: 'Русский', flag: 'https://flagcdn.com/w20/ru.png' },
            { code: 'fr', name: 'Français', flag: 'https://flagcdn.com/w20/fr.png' },
            { code: 'de', name: 'Deutsch', flag: 'https://flagcdn.com/w20/de.png' },
            { code: 'ja', name: '日本語', flag: 'https://flagcdn.com/w20/jp.png' },
            { code: 'vi', name: 'Tiếng Việt', flag: 'https://flagcdn.com/w20/vn.png' },
            { code: 'hi', name: 'हिन्दी', flag: 'https://flagcdn.com/w20/in.png' },
            { code: 'ko', name: '한국어', flag: 'https://flagcdn.com/w20/kr.png' },
            { code: 'th', name: 'ไทย', flag: 'https://flagcdn.com/w20/th.png' }
        ];
        
        const toggleLanguageDropdown = () => {
            showLanguageDropdown.value = !showLanguageDropdown.value;
        };
        
        const changeLanguage = (lang) => {
            currentLanguage.value = lang;
            i18n.global.locale = lang;
            showLanguageDropdown.value = false;
        };
        
        const getLanguageFlag = (code) => {
            const lang = availableLanguages.find(l => l.code === code);
            return lang ? lang.flag : '';
        };
        
        const getLanguageName = (code) => {
            const lang = availableLanguages.find(l => l.code === code);
            return lang ? lang.name : code;
        };

        const isRTL = computed(() => {
            const rtlCodes = ['ar', 'he', 'fa', 'ur'];
            return rtlCodes.includes(currentLanguage.value);
        });

        return {
            config,
            activeTab,
            isRTL,
            newCookie,
            newMobileAgent,
            newUriRule,
            newQueryParam,
            addCookie,
            removeCookie,
            addMobileAgent,
            removeMobileAgent,
            addUriRule,
            removeUriRule,
            addQueryParam,
            removeQueryParam,
            generateNginxConfig,
            copyToClipboard,
            checkCompressionSupport,
            // Compression support flags
            gzipSupported,
            brotliSupported,
            // Language selector
            currentLanguage,
            showLanguageDropdown,
            availableLanguages,
            toggleLanguageDropdown,
            changeLanguage,
            getLanguageFlag,
            getLanguageName
        };
     }
    });
    
    app.use(i18n);
    app.mount('#app');