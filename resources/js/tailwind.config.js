/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                'sidebar-bg':     '#1a1a4e',
                'sidebar-active': '#6d4fc2',
                'card-purple':    '#7c5cbf',
                'card-pink':      '#c45b9b',
                'card-red':       '#e05a7a',
                'approve-green':  '#2ecc71',
                'header-bg':      '#f0eeff',
                'chart-bg':       '#f8f7ff',
                'table-header':   '#c4e0f9',
            },
            fontFamily: {
                'nunito': ['Nunito', 'sans-serif'],
            }
        }
    },
    plugins: [],
}