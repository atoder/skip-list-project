/** @type {import('tailwindcss').Config} */
module.exports = {
  // 1. Enable 'class' based dark mode
  // This lets our useDarkMode() hook toggle dark mode
  // by adding or removing the 'dark' class from the <html> tag.
  darkMode: 'class',

  // 2. Configure the content scanner
  // This tells Tailwind to scan all .tsx and .css files
  // inside the 'src' folder to find utility classes.
  content: [
    "./src/**/*.{js,jsx,ts,tsx}",
    "./src/index.css"
  ],
}

