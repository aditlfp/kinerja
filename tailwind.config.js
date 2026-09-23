import forms from "@tailwindcss/forms";
import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            keyframes: {
                shadow: {
                    "0%": { width: "27%" },
                    "25%": { width: "30%" },
                    "50%": { width: "60%" },
                    "100%": { width: "27%" },
                },
                "spin-pulse": {
                    "0%, 100%": { transform: "rotate(0deg)", opacity: 1 },
                    "50%": { transform: "rotate(180deg)", opacity: 0.5 },
                },
                "rotate-border": {
                    "0%": { transform: "rotate(0deg)" },
                    "100%": { transform: "rotate(360deg)" },
                    "0%": { transform: "rotate(0deg)" },
                },
            },
            animation: {
                shadow: "shadow 5s ease infinite",
                "spin-pulse": "spin-pulse 2s linear infinite",
                "spin-slow": "spin 3s linear infinite",
                "rotate-border": "spin 4s linear infinite",
            },
            backgroundImage: {
                "guest-hp": "url('/public/logo/abs2.svg')",
                "guest-pc": "url('/public/logo/abs4.svg')",
                "guest-head": "url('/public/logo/abs1.svg')",
            },
        },
    },

    daisyui: {
        themes: ["bumblebee"],
    },

    plugins: [forms, require("@tailwindcss/forms")],
};
