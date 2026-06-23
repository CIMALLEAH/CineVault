/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                // CineVault dark theme
                cv: {
                    bg:      '#0a0a0f',
                    bg2:     '#12121a',
                    bg3:     '#1a1a26',
                    bg4:     '#22223a',
                    card:    '#15151f',
                    border:  '#2a2a42',
                    border2: '#3a3a5a',
                    gold:    '#c8a04a',
                    gold2:   '#e8c46a',
                    text:    '#f0ede8',
                    text2:   '#b8b4aa',
                    text3:   '#787068',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};