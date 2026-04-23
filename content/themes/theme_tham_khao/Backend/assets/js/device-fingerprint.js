/**
 * Device Fingerprint Generator
 * Generates a unique 32-char fingerprint for device identification
 * Used for Remember Me security and session management
 */

/**
 * Generate device fingerprint based on browser/device characteristics
 * Returns 32-char hash (MD5 length) for optimal database indexing
 */
async function generateDeviceFingerprint() {
    const components = [];
    
    try {
        // 1. Canvas fingerprint (GPU-based, most reliable)
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        if (ctx) {
            canvas.width = 200;
            canvas.height = 50;
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillStyle = '#f60';
            ctx.fillRect(125, 1, 62, 20);
            ctx.fillStyle = '#069';
            ctx.fillText('Auth Fingerprint', 2, 15);
            ctx.fillStyle = 'rgba(102, 204, 0, 0.7)';
            ctx.fillText('Device ID', 4, 35);
            components.push('canvas:' + canvas.toDataURL());
        }
        
        // 2. WebGL fingerprint
        try {
            const webglCanvas = document.createElement('canvas');
            const gl = webglCanvas.getContext('webgl') || webglCanvas.getContext('experimental-webgl');
            if (gl) {
                const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                if (debugInfo) {
                    components.push('webgl_vendor:' + gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL));
                    components.push('webgl_renderer:' + gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL));
                }
            }
        } catch (e) {
            // WebGL not available
        }
        
        // 3. Screen information
        components.push('screen:' + screen.width + 'x' + screen.height + 'x' + screen.colorDepth);
        components.push('available:' + screen.availWidth + 'x' + screen.availHeight);
        components.push('pixel_ratio:' + (window.devicePixelRatio || 1));
        
        // 4. Timezone
        components.push('timezone:' + Intl.DateTimeFormat().resolvedOptions().timeZone);
        components.push('timezone_offset:' + new Date().getTimezoneOffset());
        
        // 5. Language & locale
        components.push('language:' + (navigator.language || navigator.userLanguage));
        components.push('languages:' + (navigator.languages || []).join(','));
        
        // 6. Platform & hardware
        components.push('platform:' + navigator.platform);
        components.push('hardware:' + navigator.hardwareConcurrency);
        components.push('memory:' + (navigator.deviceMemory || 'unknown'));
        
        // 7. Browser features
        components.push('cookies:' + navigator.cookieEnabled);
        components.push('dnt:' + (navigator.doNotTrack || 'unknown'));
        components.push('storage:' + (typeof(Storage) !== 'undefined'));
        
        // Generate hash (32 chars)
        const fingerprint = await generateHash(components.join('|||'));
        return fingerprint;
        
    } catch (error) {
        console.error('Fingerprint generation error:', error);
        // Fallback to basic fingerprint
        return await generateHash(
            navigator.userAgent + '|' + 
            screen.width + 'x' + screen.height + '|' +
            new Date().getTimezoneOffset() + '|' +
            navigator.language
        );
    }
}

/**
 * Generate 32-char hash (MD5 length) for optimal database indexing
 * Uses SHA-256 but truncates to 32 chars
 */
async function generateHash(data) {
    const encoder = new TextEncoder();
    const dataBuffer = encoder.encode(data);
    const hashBuffer = await crypto.subtle.digest('SHA-256', dataBuffer);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    
    // Return first 32 characters (MD5-equivalent length for DB optimization)
    return hashHex.substring(0, 32);
}

/**
 * Initialize fingerprint on page load
 * Automatically finds and populates device_fingerprint input field
 */
(async function initFingerprint() {
    try {
        const fingerprintInput = document.getElementById('device_fingerprint');
        if (!fingerprintInput) {
            return; // No fingerprint field on this page
        }
        
        // Generate fingerprint
        const fingerprint = await generateDeviceFingerprint();
        fingerprintInput.value = fingerprint;
        
        // Store in localStorage for consistency
        localStorage.setItem('device_fingerprint', fingerprint);
        localStorage.setItem('fingerprint_generated_at', Date.now());
        
        console.log('Device fingerprint:', fingerprint.substring(0, 16) + '...');
    } catch (error) {
        console.error('Failed to generate device fingerprint:', error);
        
        // Try to use stored fingerprint
        const stored = localStorage.getItem('device_fingerprint');
        if (stored && document.getElementById('device_fingerprint')) {
            document.getElementById('device_fingerprint').value = stored;
        }
    }
})();
