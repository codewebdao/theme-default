/**
 * Tailwind — content globs relative to `builders/tailwind.config.cjs`.
 *
 * PLAN rev 2.2: không scan ../assets/**, ../builders/**.
 */
const path = require('path');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        path.join(__dirname, '../*.php'),
        path.join(__dirname, '../parts/**/*.php'),
        path.join(__dirname, '../common/**/*.php'),
        // HTML prototype trong builders (xem thử giao diện trước khi chuyển sang PHP)
        // path.join(__dirname, 'src/**/*.html'),
    ],
    theme: {
        // Mặc định Tailwind `.container` chỉ width/max-width — không `margin-inline: auto`.
        // Khớp tinh thần `assets/css/globals.css`: căn giữa + padding theo breakpoint.
        container: {
            center: true,
            padding: {
                DEFAULT: '1rem',
                sm: '1.5rem',
                lg: '2rem',
            },
            screens: {
                sm: '640px',
                md: '768px',
                lg: '1240px',
                xl: '1280px',
                '2xl': '1536px',
            },
        },
        extend: {
            colors: {
                'home-primary': 'var(--home-primary)',
                'home-primary-hover': 'var(--home-primary-hover)',
                'home-accent': 'var(--home-accent)',
                'home-heading': 'var(--home-heading)',
                'home-body': 'var(--home-body)',
                'home-surface': 'var(--home-surface)',
                'home-surface-light': 'var(--home-surface-light)',
                'home-border': 'var(--home-border)',
                'home-white': 'var(--home-white)',
                'home-success': 'var(--home-success)',
                'home-success-10': 'var(--home-success-10)',
                'home-success-20': 'var(--home-success-20)',
            },
            borderColor: {
                'home-primary': 'var(--home-primary)',
            },
            borderRadius: {
                'home-sm': 'var(--home-radius-sm)',
                'home-md': 'var(--home-radius-md)',
                'home-card': 'var(--home-radius-card)',
                'home-lg': 'var(--home-radius-lg)',
                'home-xl': 'var(--home-radius-xl)',
                'home-full': 'var(--home-radius-full)',
            },
            fontFamily: {
                plus: ['Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                space: ['Space Grotesk', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            fontSize: {
                md: ['var(--home-text-md)', { lineHeight: '1.5rem' }],
            },
        },
    },
    plugins: [require('@tailwindcss/typography')],
};
