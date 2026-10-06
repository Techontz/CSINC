const vercelProductionHost = process.env.VERCEL_PROJECT_PRODUCTION_URL;

export const SITE_URL = (
  process.env.NEXT_PUBLIC_SITE_URL ??
  (vercelProductionHost ? `https://${vercelProductionHost}` : "http://localhost:3091")
).replace(/\/$/, "");

export function absoluteUrl(path = "/"): string {
  return `${SITE_URL}${path.startsWith("/") ? path : `/${path}`}`;
}
