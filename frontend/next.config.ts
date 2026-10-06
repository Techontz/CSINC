import type { NextConfig } from "next";

const apiOrigin = new URL(process.env.API_URL ?? "http://127.0.0.1:8291/api/v1");

const securityHeaders = [
  { key: "X-Content-Type-Options", value: "nosniff" },
  { key: "X-Frame-Options", value: "SAMEORIGIN" },
  { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
  { key: "Permissions-Policy", value: "camera=(), microphone=(), geolocation=()" },
  { key: "Strict-Transport-Security", value: "max-age=31536000; includeSubDomains" },
];

const nextConfig: NextConfig = {
  poweredByHeader: false,
  images: {
    formats: ["image/avif", "image/webp"],
    qualities: [70, 75, 85],
    remotePatterns: [
      {
        protocol: apiOrigin.protocol.replace(":", "") as "http" | "https",
        hostname: apiOrigin.hostname,
        port: apiOrigin.port,
        pathname: "/storage/**",
      },
    ],
    // Only for local development where the API runs on localhost.
    dangerouslyAllowLocalIP: process.env.NEXT_IMAGE_ALLOW_LOCAL_IP === "true",
  },
  async headers() {
    return [{ source: "/:path*", headers: securityHeaders }];
  },
  async redirects() {
    // Legacy WordPress / WooCommerce URLs from the previous csinc91.com site.
    return [
      { source: "/services-2", destination: "/services", permanent: true },
      { source: "/new-home", destination: "/", permanent: true },
      { source: "/shop", destination: "/products", permanent: true },
      { source: "/product/:slug", destination: "/products/:slug", permanent: true },
      { source: "/refund_returns", destination: "/refund-policy", permanent: true },
      { source: "/my-account", destination: "/downloads", permanent: true },
      { source: "/my-account/:path*", destination: "/downloads", permanent: true },
      { source: "/checkout", destination: "/cart", permanent: true },
      { source: "/feed", destination: "/", permanent: true },
      { source: "/about-us", destination: "/about", permanent: true },
      { source: "/contact", destination: "/contact-us", permanent: true },
      { source: "/books", destination: "/products", permanent: true },
      { source: "/books/:slug", destination: "/products/:slug", permanent: true },
      { source: "/book-category/:slug", destination: "/product-category/:slug", permanent: true },
    ];
  },
};

export default nextConfig;
