/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./*.html",
        "./src/**/*.{html,js}",
    ],
    theme: {
        extend: {},
        container: {
            center: true,
            sm: '2rem',

        },
    },
    plugins: [],
}
