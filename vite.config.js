import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default defineConfig({
  plugins: [vue()],
  root: './Resources',
  base: '/dist/',
  build: {
    outDir: '../public/dist',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: path.resolve(__dirname, 'Resources/js/main.js')
      },
      output: {
        entryFileNames: '[name].js',
        chunkFileNames: 'js/[name].[hash].js',
        assetFileNames: 'assets/[name].[hash][extname]',
        format: 'iife', // IIFE format for better browser compatibility
      }
    }
  },
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './Resources/js')
    }
  },
  define: {
    'process.env': {}
  },
  server: {
    hmr: {
      overlay: false
    }
  }
})