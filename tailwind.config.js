/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                mono: ['JetBrains Mono', 'Fira Code', 'monospace'],
            },
        },
    },
    plugins: [],
    // Safelist dynamic color classes used in blade
    safelist: [
        { pattern: /bg-(emerald|orange|blue|yellow|teal|purple|red|gray)-(400|500|600)\/(10|15|20)/ },
        { pattern: /text-(emerald|orange|blue|yellow|teal|purple|red|gray)-(300|400|500)/ },
        { pattern: /border-(emerald|orange|blue|yellow|teal|purple|red|gray)-(500)\/(20|30|40)/ },
    ],
};
