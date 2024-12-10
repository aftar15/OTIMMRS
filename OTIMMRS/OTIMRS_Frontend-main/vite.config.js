import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import path from 'path'

export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  optimizeDeps: {
    force: true,
    include: [
      'vue',
      'pinia',
      'pinia-plugin-persistedstate',
      'vue-toastification'
    ]
  },
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
        ws: true,
        rewrite: (path) => path.replace(/^\/api/, ''),
        configure: (proxy, options) => {
          proxy.on('error', (err, req, res) => {
            console.error('Proxy Error:', err);
            if (res.writeHead && !res.headersSent) {
              res.writeHead(500, {
                'Content-Type': 'application/json',
              });
              res.end(JSON.stringify({ 
                error: 'Proxy Error', 
                message: 'Failed to connect to backend server. Please ensure Laravel is running.'
              }));
            }
          });
          proxy.on('proxyReq', (proxyReq, req, res) => {
            console.log('Sending Request:', {
              method: req.method,
              url: req.url,
              headers: proxyReq.getHeaders()
            });
          });
          proxy.on('proxyRes', (proxyRes, req, res) => {
            console.log('Received Response:', {
              statusCode: proxyRes.statusCode,
              url: req.url,
              headers: proxyRes.headers
            });
          });
        },
      }
    },
    port: 5173,
    host: true,
    cors: true,
    hmr: {
      overlay: false
    }
  },
  build: {
    sourcemap: true,
    commonjsOptions: {
      include: [/node_modules/],
    },
  }
})

