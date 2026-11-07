/** @type {import('next').NextConfig} */
const BACKEND = (process.env.BACKEND_URL || 'http://127.0.0.1:8001').replace(/\/$/, '')
const nextConfig = {
  experimental: { forceSwcTransforms: false },
  async rewrites() {
    return [
      { source: '/storage/:path*', destination: BACKEND + '/storage/:path*' },
      { source: '/api/:path*',     destination: BACKEND + '/api/:path*' },
    ]
  },
}
export default nextConfig
