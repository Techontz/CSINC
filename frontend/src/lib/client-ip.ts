import "server-only";
import { headers } from "next/headers";

/** Best-effort visitor IP, forwarded to the API so its rate limits apply per visitor. */
export async function clientIp(): Promise<string | null> {
  const list = await headers();
  const forwarded = list.get("x-forwarded-for")?.split(",")[0]?.trim();
  return forwarded || list.get("x-real-ip") || null;
}
