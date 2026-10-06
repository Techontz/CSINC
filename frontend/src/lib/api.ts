import "server-only";
import { notFound } from "next/navigation";
import { connection } from "next/server";
import { API_BASE_URL as API_URL } from "./api-config";
import type {
  Book,
  BookCategory,
  BookDetail,
  ConsultationOptions,
  OrderData,
  PageData,
  Paginated,
  Service,
  SiteData,
} from "./types";

/** Content is cached for an hour and purged on demand by the CMS via tags. */
const REVALIDATE_SECONDS = 3600;

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
  ) {
    super(message);
  }
}

type FetchOptions = { tags?: string[]; cache?: "no-store" };

async function request<T>(path: string, { tags = [], cache }: FetchOptions = {}): Promise<T> {
  let response: Response;

  try {
    response = await fetch(`${API_URL}${path}`, {
      headers: { Accept: "application/json" },
      ...(cache === "no-store" ? { cache } : { next: { revalidate: REVALIDATE_SECONDS, tags } }),
    });
  } catch (error) {
    // If the API is unreachable while building (e.g. CI without the backend),
    // defer this route to request time instead of failing the whole build.
    if (process.env.NEXT_PHASE === "phase-production-build") {
      await connection();
    }
    throw error;
  }

  if (!response.ok) {
    throw new ApiError(response.status, `API ${response.status} for ${path}`);
  }

  return (await response.json()) as T;
}

/** Like request(), but turns a 404 into the Next.js not-found page. */
async function requestOrNotFound<T>(path: string, options: FetchOptions): Promise<T> {
  try {
    return await request<T>(path, options);
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) {
      notFound();
    }
    throw error;
  }
}

export async function getSite(): Promise<SiteData> {
  const { data } = await request<{ data: SiteData }>("/site", { tags: ["site"] });
  return data;
}

export async function getPage(slug: string): Promise<PageData> {
  const { data } = await requestOrNotFound<{ data: PageData }>(`/pages/${encodeURIComponent(slug)}`, {
    tags: ["pages", `page:${slug}`, "books", "services"],
  });
  return data;
}

export async function findPage(slug: string): Promise<PageData | null> {
  try {
    const { data } = await request<{ data: PageData }>(`/pages/${encodeURIComponent(slug)}`, {
      tags: ["pages", `page:${slug}`, "books", "services"],
    });
    return data;
  } catch (error) {
    if (error instanceof ApiError && error.status === 404) {
      return null;
    }
    throw error;
  }
}

export type BookQuery = {
  search?: string;
  category?: string;
  sort?: string;
  page?: number;
  per_page?: number;
  featured?: boolean;
};

export async function getBooks(query: BookQuery = {}): Promise<Paginated<Book>> {
  const params = new URLSearchParams();
  Object.entries(query).forEach(([key, value]) => {
    if (value !== undefined && value !== "" && value !== false) {
      params.set(key, String(value === true ? 1 : value));
    }
  });

  return request<Paginated<Book>>(`/books?${params.toString()}`, { tags: ["books"] });
}

export async function getBook(slug: string): Promise<{ data: BookDetail; related: Book[] }> {
  return requestOrNotFound(`/books/${encodeURIComponent(slug)}`, { tags: ["books", `book:${slug}`] });
}

export async function getCategories(): Promise<BookCategory[]> {
  const { data } = await request<{ data: BookCategory[] }>("/book-categories", { tags: ["book-categories", "books"] });
  return data;
}

export async function getCategory(slug: string): Promise<BookCategory> {
  const { data } = await requestOrNotFound<{ data: BookCategory }>(`/book-categories/${encodeURIComponent(slug)}`, {
    tags: ["book-categories", "books"],
  });
  return data;
}

export async function getServices(group?: string): Promise<Service[]> {
  const { data } = await request<{ data: Service[] }>(`/services${group ? `?group=${group}` : ""}`, { tags: ["services"] });
  return data;
}

export async function getConsultationOptions(): Promise<ConsultationOptions> {
  const { data } = await request<{ data: ConsultationOptions }>("/contact/options", { tags: ["site"] });
  return data;
}

export async function getSitemapIndex(): Promise<{
  pages: { slug: string; updated_at: string | null }[];
  books: { slug: string; updated_at: string | null }[];
  categories: { slug: string; updated_at: string | null }[];
}> {
  const { data } = await request<{ data: Awaited<ReturnType<typeof getSitemapIndex>> }>("/sitemap", {
    tags: ["pages", "books", "book-categories"],
  });
  return data;
}

export async function getOrder(uuid: string, token: string): Promise<OrderData> {
  const { data } = await requestOrNotFound<{ data: OrderData }>(
    `/orders/${encodeURIComponent(uuid)}?token=${encodeURIComponent(token)}`,
    { cache: "no-store" },
  );
  return data;
}

/** Server-side proxy used by route handlers to forward visitor submissions. */
export async function forward(path: string, body: unknown, clientIp: string | null): Promise<Response> {
  return fetch(`${API_URL}${path}`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(clientIp ? { "X-Forwarded-For": clientIp } : {}),
    },
    body: JSON.stringify(body),
    cache: "no-store",
  });
}
