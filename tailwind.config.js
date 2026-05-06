/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./index.php",
    "./admin/**/*.php",
    "./appointments/**/*.php",
    "./auth/**/*.php",
    "./branches/**/*.php",
    "./charts/**/*.php",
    "./config/**/*.php",
    "./custom_order/**/*.php",
    "./customer/**/*.php",
    "./dashboard/**/*.php",
    "./includes/**/*.php",
    "./inventory/**/*.php",
    "./legal/**/*.php",
    "./modules/**/*.php",
    "./orders/**/*.php",
    "./products/**/*.php",
    "./profile/**/*.php",
    "./reports/**/*.php",
    "./search.php",
    "./search_api.php",
    "./settings/**/*.php",
    "./staff/**/*.php",
    "./tasks/**/*.php",
    "./tools/**/*.php",
    "./transactions/**/*.php",
    "./assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        'slate-950': '#020617',
        'slate-850': '#0f172a',
      },
      fontFamily: {
        outfit: ['Outfit', 'sans-serif'],
        inter: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
