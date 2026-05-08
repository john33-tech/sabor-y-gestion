/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Http/Livewire/**/*.php", // Si usas Livewire
  ],
  theme: {
    extend: {
        screens: {
            'xs': '475px',
            'sm': '640px',
            'md': '768px',
            'lg': '1024px',
            'xl': '1280px',
            '2xl': '1536px',
        },
        colors: {
          primary: '#C2410C',   // orange-700
          secondary: '#F97316', // orange-500
          accent: '#FDBA74',    // orange-300
          background: '#FFF7ED',// orange-50
          surface: '#FFFFFF',   // white
          text: '#111827',      // gray-900
          muted: '#78716C',     // stone-500
          border: '#FED7AA',    // orange-200
      },
      fontFamily: {
        'sans': ['Poppins', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
