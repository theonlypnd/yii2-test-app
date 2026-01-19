import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  root: path.resolve(__dirname),
  base: '/spa/',
  build: {
    outDir: path.resolve(__dirname, '../web/spa'),
    emptyOutDir: true,
    assetsDir: 'assets',
    rollupOptions: {
      input: path.resolve(__dirname, 'index.html'),
    },
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
});
