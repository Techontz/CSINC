import "server-only";
import { NextResponse } from "next/server";
import { forward } from "./api";
import { clientIp } from "./client-ip";

const MAX_BODY_BYTES = 64 * 1024;

/**
 * Forwards a visitor's JSON submission to the Laravel API and relays the
 * response, so the API URL stays private and errors never leak internals.
 */
export async function proxyJson(request: Request, path: string): Promise<NextResponse> {
  const raw = await request.text();

  if (raw.length > MAX_BODY_BYTES) {
    return NextResponse.json({ message: "Request too large." }, { status: 413 });
  }

  let body: unknown;
  try {
    body = JSON.parse(raw);
  } catch {
    return NextResponse.json({ message: "Invalid request." }, { status: 400 });
  }

  try {
    const response = await forward(path, body, await clientIp());
    const payload = await response.json().catch(() => ({}));
    const safe = response.status >= 500 ? { message: payload.message ?? "Something went wrong. Please try again." } : payload;

    return NextResponse.json(safe, { status: response.status });
  } catch {
    return NextResponse.json({ message: "The service is temporarily unavailable. Please try again shortly." }, { status: 503 });
  }
}
