import { cn } from "@/lib/cn";

/**
 * Renders HTML that has already been sanitised by the API (Symfony
 * HtmlSanitizer allow-list) before it reaches the frontend.
 */
export function RichText({ html, className, invert = false }: { html: string | null | undefined; className?: string; invert?: boolean }) {
  if (!html) {
    return null;
  }

  return <div className={cn("prose-site", invert && "prose-invert-site", className)} dangerouslySetInnerHTML={{ __html: html }} />;
}
