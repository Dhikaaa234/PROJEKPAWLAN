/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          navy: '#0b2545',
          deep: '#08172e',
          blue: '#1d4ed8',
          blueHover: '#1e40af',
          gold: '#f5c542',
          goldDark: '#d4a017'
        }
      },
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['"Sora"', '"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui']
      },
      boxShadow: {
        'soft': '0 4px 20px -4px rgba(15, 23, 42, 0.08)',
        'glow': '0 8px 30px -8px rgba(29, 78, 216, 0.45)'
      }
    }
  },
  plugins: []
}
