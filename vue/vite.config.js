import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
    },
  },
  root: path.resolve(__dirname),
  build: {
    outDir: path.resolve(__dirname, '../web/spa'),
    emptyOutDir: true,
    assetsDir: 'assets', // папка для скриптів/стилів
    rollupOptions: {
      input: path.resolve(__dirname, 'index.html'),
    },
    // Задаємо правильний publicPath, щоб генеровані шляхи починалися з /spa/
    // В Vue 3 + Vite це base:
    base: '/spa/',
  },
});
