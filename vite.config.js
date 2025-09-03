import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [
    laravel({
      // Breeze/Inertia usually uses this entry:
      input: ['resources/js/app.js'],
      refresh: true,
    }),
    vue(),
  ],
})
