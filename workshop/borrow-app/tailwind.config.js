import preset from './vendor/filament/filament/tailwind.config.preset.js'

export default {
  presets: [preset],
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.ts',
    './resources/**/*.vue',
    './resources/**/*.jsx',
    './resources/**/*.tsx',
    './app/Filament/**/*.php',
    './vendor/filament/**/*.blade.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        // ใช้งานฟอนต์ Kanit ทั่วทั้งแอป
        sans: ['Kanit', 'system-ui', 'ui-sans-serif', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans Thai', 'sans-serif'],
      },
      colors: {
        // โทนแบรนด์ (เขียว-ฟ้า) ที่คุณใช้บ่อย
        brand: {
          500: '#10b981', // emerald-500
          600: '#059669', // emerald-600
          start: '#16a34a', // gradient start
          end:   '#06b6d4', // gradient end
        },
      },
    },
  },
  plugins: [],
}
