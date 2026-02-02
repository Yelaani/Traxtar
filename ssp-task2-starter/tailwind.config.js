module.exports = {
  content: ["./public/**/*.php","./app/Views/**/*.php","./app/**/*.php"],
  safelist: [
    'text-red-800',
    'bg-red-100',
    'text-green-800',
    'bg-green-100'
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          DEFAULT: '#0ea5a4',
          50: '#ecfeff',
          100: '#cffafe',
          200: '#a5f3fc',
          300: '#67e8f9',
          400: '#22d3ee',
          500: '#06b6d4',
          600: '#0ea5a4',
          700: '#0b7a79',
          800: '#065f5b',
          900: '#064e3b'
        }
      },
      fontFamily: {
        inter: ['Inter', 'system-ui', 'sans-serif']
      }
    }
  },
  plugins: [],
};
