import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// The dashboard is a separate SPA that ships inside Laravel's public/ directory
// as a real folder, so the web server serves it without touching PHP — same as
// the generated site. In dev it stays at the root of the Vite server and proxies
// the API; only a production build is rebased onto /admin/.
// https://vite.dev/config/
export default defineConfig(({ command }) => ({
  base: command === 'build' ? '/admin/' : '/',
  plugins: [react(), tailwindcss()],
  build: {
    outDir: '../public/admin',
    emptyOutDir: true,
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
}))
