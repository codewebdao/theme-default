'use strict';

/**
 * Build mỗi entry trong `src/css/entries/*.css` → `../assets/css/<tên-entry>.css`.
 *
 * Mặc định (`npm run build`): Tailwind `content` = glob trong `tailwind.config.cjs` (toàn theme PHP).
 *
 * Tuỳ chọn thu hẹp theo HTML preview: `THEME_CSS_PREVIEW=1` + `@page-url` trong comment entry:
 *   fetch/curl → `.build-preview/<slug>.html` → `content` chỉ file đó (bundle nhỏ hơn, rủi ro thiếu class).
 */

const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');
const postcss = require('postcss');
const postcssImport = require('postcss-import');
const tailwindcss = require('tailwindcss');
const autoprefixer = require('autoprefixer');

const BUILDERS_DIR = path.resolve(__dirname, '..');
const THEME_ROOT = path.join(BUILDERS_DIR, '..');
const ENTRIES_DIR = path.join(BUILDERS_DIR, 'src', 'css', 'entries');
const PREVIEW_DIR = path.join(BUILDERS_DIR, '.build-preview');
const ASSETS_CSS_DIR = path.join(THEME_ROOT, 'assets', 'css');

const baseTailwindConfig = require(path.join(BUILDERS_DIR, 'tailwind.config.cjs'));

const PREVIEW_TAG = '@page-url';
const HEADER_SCAN_BYTES = 12000;

/** Bật = fetch HTML và JIT chỉ theo DOM preview (cần `@page-url` trong entry). */
function usePreviewHtmlJit() {
    return String(process.env.THEME_CSS_PREVIEW || '').trim() === '1';
}

function parsePagePreviewUrl(cssSource) {
    const head = cssSource.slice(0, HEADER_SCAN_BYTES);
    const re = new RegExp(`${PREVIEW_TAG}\\s+(\\S+)`, 'i');
    const m = head.match(re);
    return m ? m[1].trim().replace(/^['"]|['"]$/g, '') : '';
}

function resolvePreviewUrl(spec) {
    if (!spec) {
        return '';
    }
    if (/^https?:\/\//i.test(spec)) {
        return spec;
    }
    const base = (process.env.THEME_PREVIEW_BASE_URL || '').replace(/\/+$/, '');
    if (!base) {
        throw new Error(
            `URL preview tương đối "${spec}" cần biến môi trường THEME_PREVIEW_BASE_URL (vd. http://laragon.test)`,
        );
    }
    const pathPart = spec.startsWith('/') ? spec : `/${spec}`;
    return `${base}${pathPart}`;
}

function skipSslVerify() {
    return String(process.env.THEME_PREVIEW_SKIP_SSL_VERIFY || '').trim() === '1';
}

function fetchWithCurl(url) {
    const bin = process.env.CURL_BIN || 'curl';
    const timeoutSec = Math.ceil(Number(process.env.THEME_PREVIEW_TIMEOUT_MS || 30000) / 1000);
    const args = ['-sS', '-L', '--max-time', String(timeoutSec), '-A', 'giao-dien-education-css-build/1.0', url];
    if (skipSslVerify()) {
        args.splice(1, 0, '-k');
    }
    const cookie = (process.env.THEME_PREVIEW_COOKIE || '').trim();
    if (cookie) {
        args.push('-H', `Cookie: ${cookie}`);
    }
    return execFileSync(bin, args, {
        encoding: 'utf8',
        maxBuffer: 50 * 1024 * 1024,
    });
}

/** fetch + optional dispatcher (TLS không verify khi bật THEME_PREVIEW_SKIP_SSL_VERIFY). */
function getPreviewHttpClient() {
    if (!skipSslVerify()) {
        return { fetchFn: globalThis.fetch.bind(globalThis), dispatcher: undefined };
    }
    try {
        const { Agent, fetch: undiciFetch } = require('node:undici');
        return {
            fetchFn: undiciFetch,
            dispatcher: new Agent({
                connect: {
                    rejectUnauthorized: false,
                },
            }),
        };
    } catch (_) {
        return { fetchFn: globalThis.fetch.bind(globalThis), dispatcher: undefined, tlsLegacyEnv: true };
    }
}

async function fetchPreviewHtml(url) {
    const useCurl = String(process.env.THEME_PREVIEW_USE_CURL || '').trim() === '1';
    if (useCurl) {
        return fetchWithCurl(url);
    }
    const { fetchFn, dispatcher, tlsLegacyEnv } = getPreviewHttpClient();
    const ctrl = new AbortController();
    const ms = Number(process.env.THEME_PREVIEW_TIMEOUT_MS || 30000);
    const t = setTimeout(() => ctrl.abort(), ms);
    let prevTls;
    if (tlsLegacyEnv) {
        prevTls = process.env.NODE_TLS_REJECT_UNAUTHORIZED;
        process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
    }
    try {
        const res = await fetchFn(url, {
            redirect: 'follow',
            signal: ctrl.signal,
            ...(dispatcher ? { dispatcher } : {}),
            headers: {
                'user-agent': 'giao-dien-education-css-build/1.0',
                ...(process.env.THEME_PREVIEW_COOKIE
                    ? { cookie: process.env.THEME_PREVIEW_COOKIE }
                    : {}),
            },
        });
        if (!res.ok) {
            throw new Error(`HTTP ${res.status} ${res.statusText}`);
        }
        return await res.text();
    } catch (err) {
        const msg = err instanceof Error ? err.message : String(err);
        if (/fetch failed|certificate|SSL|TLS|UNABLE_TO_VERIFY/i.test(msg) && !skipSslVerify()) {
            throw new Error(
                `${msg} — thử: THEME_PREVIEW_SKIP_SSL_VERIFY=1 npm run build (HTTPS tự ký / hosts → máy khác).`,
                { cause: err },
            );
        }
        throw err;
    } finally {
        clearTimeout(t);
        if (tlsLegacyEnv) {
            if (prevTls === undefined) {
                delete process.env.NODE_TLS_REJECT_UNAUTHORIZED;
            } else {
                process.env.NODE_TLS_REJECT_UNAUTHORIZED = prevTls;
            }
        }
    }
}

function entrySlug(entryFile) {
    return path.basename(entryFile, '.css');
}

async function buildOneEntry(entryPath) {
    const slug = entrySlug(entryPath);
    const cssIn = fs.readFileSync(entryPath, 'utf8');
    const previewSpec = parsePagePreviewUrl(cssIn);
    const previewJit = usePreviewHtmlJit();

    let tailwindConfig = baseTailwindConfig;
    if (previewSpec && previewJit) {
        const url = resolvePreviewUrl(previewSpec);
        process.stderr.write(`[build-page-css] ${slug}: THEME_CSS_PREVIEW=1 — fetch ${url}\n`);
        const html = await fetchPreviewHtml(url);
        if (!html || html.length < 64) {
            throw new Error(`HTML preview quá ngắn hoặc rỗng (${slug})`);
        }
        fs.mkdirSync(PREVIEW_DIR, { recursive: true });
        const htmlPath = path.join(PREVIEW_DIR, `${slug}.html`);
        fs.writeFileSync(htmlPath, html, 'utf8');
        tailwindConfig = {
            ...baseTailwindConfig,
            content: [htmlPath],
        };
        process.stderr.write(`[build-page-css] ${slug}: Tailwind content → ${htmlPath}\n`);
    } else {
        if (previewSpec && !previewJit) {
            process.stderr.write(
                `[build-page-css] ${slug}: content rộng (tailwind.config.cjs). Preview HTML: THEME_CSS_PREVIEW=1 npm run build\n`,
            );
        } else if (!previewSpec) {
            process.stderr.write(`[build-page-css] ${slug}: không có ${PREVIEW_TAG} — dùng content mặc định\n`);
        }
    }

    const outPath = path.join(ASSETS_CSS_DIR, `${slug}.css`);
    fs.mkdirSync(path.dirname(outPath), { recursive: true });

    const processor = postcss([postcssImport(), tailwindcss(tailwindConfig), autoprefixer()]);

    const result = await processor.process(cssIn, { from: entryPath, to: outPath });
    fs.writeFileSync(outPath, result.css, 'utf8');

    process.stderr.write(`[build-page-css] ${slug}: wrote ${path.relative(BUILDERS_DIR, outPath)}\n`);
}

async function main() {
    if (!fs.existsSync(ENTRIES_DIR)) {
        throw new Error(`Thiếu thư mục entries: ${ENTRIES_DIR}`);
    }
    const entries = fs
        .readdirSync(ENTRIES_DIR)
        .filter((f) => f.endsWith('.css'))
        .map((f) => path.join(ENTRIES_DIR, f))
        .sort();
    if (entries.length === 0) {
        throw new Error(`Không có file .css trong ${ENTRIES_DIR}`);
    }
    for (const p of entries) {
        await buildOneEntry(p);
    }
}

main().catch((err) => {
    process.stderr.write(`${err.stack || err}\n`);
    process.exit(1);
});
