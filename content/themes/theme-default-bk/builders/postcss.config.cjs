/**
 * PostCSS — theme `giao-dien-education` (rev 2.2 starter: CSS only).
 *
 * postcss-import phải chạy trước Tailwind để @import partial tương đối
 * được gộp vào file output — tránh để lại URL tương đối (404 trên browser).
 */
module.exports = {
    plugins: [
        require('postcss-import'),
        require('tailwindcss'),
        require('autoprefixer'),
    ],
};
