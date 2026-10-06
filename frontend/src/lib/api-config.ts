/**
 * The single source of the Laravel backend address.
 *
 * NEXT_PUBLIC_API_URL is the backend origin, e.g. https://api.agizastore.xyz.
 * API_URL (the full /api/v1 base) is still accepted for older deployments.
 * Outside production the local development backend is used as a fallback;
 * in production a missing value fails loudly instead of silently calling localhost.
 */
const LOCAL_BACKEND = "http://127.0.0.1:8291";

function resolveBackendOrigin(): string {
  const configured = process.env.NEXT_PUBLIC_API_URL ?? process.env.API_URL?.replace(/\/api\/v1\/?$/, "");

  if (configured) {
    return configured.replace(/\/+$/, "");
  }

  if (process.env.NODE_ENV === "production") {
    throw new Error("NEXT_PUBLIC_API_URL is not set. Point it at the Laravel backend, e.g. https://api.agizastore.xyz");
  }

  return LOCAL_BACKEND;
}

/** Backend origin, e.g. https://api.agizastore.xyz */
export const BACKEND_URL = resolveBackendOrigin();

/** Versioned REST API base, e.g. https://api.agizastore.xyz/api/v1 */
export const API_BASE_URL = `${BACKEND_URL}/api/v1`;
