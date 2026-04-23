###################################################################################################
# CMSFF-Nginx
# CMSFF-Nginx is a NGINX configuration to speedup your CMSFF website with the cache plugin CMSFF
# Author: Alex Watson
# Maintainer: FlashCms.net
# URL: https://github.com/cmsfullform/nginx
#
# Tested with CMSFF version: 2.1.0
# Tested with NGINX: 1.25.2 (mainline)
#
# Version 2.1.0
#
###################################################################################################
# Add debug information into header
set $cmsff_debug {{DEBUG}};
set $cache_root "$document_root";
if ($document_root ~* "^(.*)/public$") {
    set $cache_root "$1";
}

###################################################################################################
# Do not alter these values
#
set $cmsff_bypass 1; # Should NGINX bypass CMSFF and call cache file directly ? (1 = Load from Static Cache Files, 0 = go to PHP CMS and Get and Set Cache by PHP)
set $cmsff_encryption ""; # Is GZIP accepted by client ?
set $cmsff_file ""; # Filename to look for
set $cmsff_is_bypassed "MISS"; # Header text added to check if the bypass worked or not. X-CMSFF-Nginx-Serving-Static header usage
set $cmsff_reason ""; # Reason why cache file was not used
set $cmsff_https_prefix ""; # HTTPS prefix for cached files
set $cmsff_mobile_prefix ""; # Mobile prefix for mobile cache files
set $cmsff_is_https 0; # HTTPS check
set $cmsff_dynamic ""; # Dynamic value to add to cached filename
set $cmsff_device "desktop"; # Device type (desktop or mobile)
set $cmsff_default_type "text/html"; # Set default type is html or json

# Check Browser or Request supports WebP ?
{{WEBP_CHECK}}

###################################################################################################
#CMSFF INIT CORS NGINX
{{API_LOCATION}}

#CMSFF INIT CONTENT PATH (themes, plugins, uploads combined)
{{CONTENT_LOCATION}}

# PAGE CACHE
#
set $cmsff_uri_path "";
if ($request_uri ~ "^([^?]*)(\?.*)?$") {
set $cmsff_uri_path $1;
}

# Is Brotli accepted by client ?
{{BROTLI_CHECK}}
# Is GZIP accepted by client ?
{{GZIP_CHECK}}

# Is HTTPS request ?
if ($https = "on") { set $cmsff_is_https 1; }
if ($http_x_forwarded_proto = "https") { set $cmsff_is_https 1; }
if ($http_front_end_https = "on") { set $cmsff_is_https 1; }
if ($http_x_forwarded_protocol = "https") { set $cmsff_is_https 1; }
if ($http_x_forwarded_ssl = "on") { set $cmsff_is_https 1; }
if ($http_x_url_scheme = "https") { set $cmsff_is_https 1; }
if ($http_forwarded ~ /proto=https/) { set $cmsff_is_https 1; }

if ($cmsff_is_https = "1") {
    set $cmsff_https_prefix "-https";
}

# Query strings to ignore
set $cmsff_args "";

# Query string to cache
{{QUERY_PARAMS_CACHE}}

# File/URL to return IF we must bypass CMSFF
# Desktop: index.html
# Gzip: index.html_gzip
# HTTPS: index-https.html
# Mobile: index-mobile-https.html

# Convert query string to directory format if only contains allowed parameters
# Cacheable Query Parameters

# Set cache path with converted directory format
set $cmsff_pre_url "/writable/cache/$host$cmsff_uri_path/$cmsff_args/";
set $cmsff_pre_file "$cache_root/writable/cache/$host$cmsff_uri_path/$cmsff_args/";

# Mobile detection file path
set $cmsff_mobile_detection "$cmsff_pre_file/.mobile-active";

# Login active detection file path #newcode
set $cmsff_login_detection "$cmsff_pre_file/.login-active";

# Then check user agent with complete pattern matching
{{MOBILE_USER_AGENTS_CHECK}}

# Set mobile prefix if mobile mode is activated
if (-f "$cmsff_mobile_detection") {
    set $cmsff_mobile_prefix "-mobile";
}
if ($cmsff_device != "mobile") {
    set $cmsff_mobile_prefix "";
}

# Setting cache file struct path
# Detect if this is an API route to use .json extension
set $cmsff_file_ext ".html";
{{API_JSON_DETECTION}}

set $cmsff_file_start "index$cmsff_mobile_prefix$cmsff_https_prefix";
set $cmsff_url "$cmsff_pre_url$cmsff_file_start$cmsff_dynamic$cmsff_file_ext";
set $cmsff_file "$cmsff_pre_file$cmsff_file_start$cmsff_dynamic$cmsff_file_ext";

# Check if gzip version cached file is available
if (-f "$cmsff_file$cmsff_encryption") {
    set $cmsff_file "$cmsff_file$cmsff_encryption";
    set $cmsff_url "$cmsff_url$cmsff_encryption";
}

# Cookie check - Only bypass if user is logged in AND login-active file doesn't exist
# If login-active file exists, allow caching even for logged-in users
# Set flag to check if we should bypass due to login
set $cmsff_login_bypass "0";
{{COOKIE_CHECK}}
# If login-active file exists, reset login bypass flag (allow cache)
if (-f "$cmsff_login_detection") {
    set $cmsff_login_bypass "0";
}
# Apply login bypass if flag is set
if ($cmsff_login_bypass = "1") {
    set $cmsff_bypass 0;
    set $cmsff_is_bypassed "BYPASS";
    set $cmsff_reason "Cookie Not Caching";
}

# URI cache rules: applied in the order defined by the user (last matching rule wins)
# Default: 0 = cache only when an Allow rule matches; 1 = cache except when a Block rule matches
{{SHOULD_CACHE_DEFAULT}}
{{URI_CACHE_RULES}}

# Disable bypass if should_cache is 0 (admin/backend/account URLs should go to PHP)
if ($should_cache = "0") {
    set $cmsff_bypass 0;
}

# Do not bypass if the cached file does not exist
if (!-f "$cmsff_file") {
    set $cmsff_bypass 0;
    set $cmsff_is_bypassed "MISS";
    set $cmsff_reason "Cached file not found";
}

# Do not bypass if it's a POST request
if ($request_method = POST) {
    set $cmsff_bypass 0;
    set $cmsff_is_bypassed "BYPASS";
    set $cmsff_reason "POST request";
}

# Maintenance mode
if (-f "$cache_root/.maintenance") {
    set $cmsff_bypass 0;
    set $cmsff_is_bypassed "BYPASS";
    set $cmsff_reason "Maintenance mode";
}

# If the bypass token is still on, let's bypass CMSFF with the cached URL
if ($cmsff_bypass = 1) {
    set $cmsff_is_bypassed "HIT";
    set $cmsff_reason "$cmsff_url";
}

# Clear variables if debug is not needed
if ($cmsff_debug = 0) {
    set $cmsff_reason "";
    set $cmsff_file "";
}

# If bypass token still on, rewrite
if ($cmsff_bypass = 1) {
    rewrite .* "$cmsff_url" last;
}

###################################################################################################

location /writable/ {
    root "$cache_root";
    autoindex off;
    index index$cmsff_mobile_prefix$cmsff_https_prefix$cmsff_dynamic.html_gzip index$cmsff_mobile_prefix$cmsff_https_prefix$cmsff_dynamic.html index.html_gzip index.html;
}

# Add header to HTML cached files
location ~ /writable/cache/.*html$ {
    root "$cache_root";
    etag on;
    add_header Vary "Accept-Encoding, Cookie";
    add_header Cache-Control "no-cache, no-store, must-revalidate";
    add_header X-CMSFF-Nginx-Serving-Static $cmsff_is_bypassed;
    add_header X-CMSFF-Nginx-Reason $cmsff_reason;
    add_header X-CMSFF-Nginx-File $cmsff_file;
    add_header Content-Type $cmsff_default_type;
}

# Add header to JSON cached files (API)
location ~ /writable/cache/.*json$ {
    root "$cache_root";
    etag on;
    add_header Vary "Accept-Encoding, Cookie";
    add_header Cache-Control "no-cache, no-store, must-revalidate";
    add_header X-CMSFF-Nginx-Serving-Static $cmsff_is_bypassed;
    add_header X-CMSFF-Nginx-Reason $cmsff_reason;
    add_header X-CMSFF-Nginx-File $cmsff_file;
    add_header Content-Type application/json;
    {{API_CORS_HEADERS_JSON}}
}
{{GZIP_LOCATION_BLOCK}}
{{JSON_GZIP_LOCATION_BLOCK}}

# Debug headers
add_header X-CMSFF-Nginx-Reason $cmsff_reason;
add_header X-CMSFF-Nginx-Serving-Static $cmsff_is_bypassed;
add_header X-CMSFF-Nginx-Device $cmsff_device;
add_header X-CMSFF-Nginx-File $cmsff_file;

###################################################################################################
# BROWSER CSS CACHE
#
location ~* \.css$ {
    etag on;
    gzip_vary on;
    access_log off;
    expires {{CSS_EXPIRATION}};
}

###################################################################################################
# BROWSER JS CACHE
#
location ~* \.js$ {
    etag on;
    gzip_vary on;
    access_log off;
    expires {{JS_EXPIRATION}};
}

###################################################################################################
# BROWSER MEDIA CACHE
location ~* \.(map|ico|gif|jpe?g|png|webp|svg|eot|otf|woff|woff2|ttf|ogg)$ {
    etag on;
    expires {{MEDIA_EXPIRATION}};
    access_log off;
    add_header Cache-Control "public";
}

# CMSFF Sync Caching State about NginxCache & CMSFF Cache
set $skip_cache 0;
if ($cmsff_bypass = 1){
    set $skip_cache 0;
}
if ($cmsff_bypass = 0){
    set $skip_cache 1;
}