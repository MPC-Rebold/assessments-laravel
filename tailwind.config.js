import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';


/** @type {import('tailwindcss').Config} */
export default {
    presets: [
        require('./vendor/wireui/wireui/tailwind.config.js')
    ],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./node_modules/flowbite/**/*.js",

        './vendor/wireui/wireui/resources/**/*.blade.php',
        './vendor/wireui/wireui/ts/**/*.ts',
        './vendor/wireui/wireui/src/View/**/*.php'
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    '500': '#680735',
                    '600': '#560304'
                },
                secondary: {
                    '50': '#f6f7f9',
                    '100': '#ebeef3',
                    '200': '#d3dae4',
                    '300': '#acbacd',
                    '400': '#7f95b1',
                    '500': '#5f7898',
                    '600': '#506686',
                    '700': '#3e4e66',
                    '800': '#364356',
                    '900': '#30394a',
                    '950': '#202631',
                },
                positive: {
                    '50': '#DEE2DA',
                    '100': '#ABBAA8',
                    '200': '#90A690',
                    '300': '#77927D',
                    '500': '#5F7E6B',
                    '600': '#47695b',
                    '700': '#3b5549',
                    '800': '#2f4137',
                    '900': '#233023',
                },
            },
        },
    },
    plugins: [forms],
};
