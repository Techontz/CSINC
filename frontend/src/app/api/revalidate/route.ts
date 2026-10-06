import { revalidateTag } from "next/cache";
import { NextResponse } from "next/server";
import { timingSafeEqual } from "node:crypto";

const ALLOWED = /^(site|pages|books|services|book-categories|page:[a-z0-9-]+|book:[a-z0-9-]+)$/;

function validSecret(provided: string | null): boolean {
  const expected = process.env.REVALIDATE_SECRET;
  if (!expected || !provided) {
    return false;
  }
  const a = Buffer.from(provided);
  const b = Buffer.from(expected);
  return a.length === b.length && timingSafeEqual(a, b);
}

/** Called by the Laravel CMS after content changes to purge tagged caches. */
export async function POST(request: Request) {
  if (!validSecret(request.headers.get("x-revalidate-secret"))) {
    return NextResponse.json({ message: "Unauthorized" }, { status: 401 });
  }

  const body = (await request.json().catch(() => ({}))) as { tags?: unknown };
  const tags = Array.isArray(body.tags) ? body.tags.filter((tag): tag is string => typeof tag === "string" && ALLOWED.test(tag)) : [];

  if (tags.length === 0) {
    return NextResponse.json({ message: "No valid tags supplied." }, { status: 422 });
  }

  // Content edits should be visible on the very next request.
  tags.forEach((tag) => revalidateTag(tag, { expire: 0 }));

  return NextResponse.json({ revalidated: tags, at: Date.now() });
}
