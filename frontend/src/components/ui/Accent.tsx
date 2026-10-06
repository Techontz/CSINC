import { Fragment } from "react";

/**
 * Renders CMS headings where words wrapped in *asterisks* are set in the
 * italic serif accent style.
 */
export function Accent({ text }: { text: string | null | undefined }) {
  if (!text) {
    return null;
  }

  const parts = text.split(/(\*[^*]+\*)/g).filter(Boolean);

  return (
    <>
      {parts.map((part, index) =>
        part.startsWith("*") && part.endsWith("*") ? (
          <em key={index} className="accent">
            {part.slice(1, -1)}
          </em>
        ) : (
          <Fragment key={index}>{part}</Fragment>
        ),
      )}
    </>
  );
}

export function stripAccent(text: string | null | undefined): string {
  return (text ?? "").replace(/\*/g, "");
}
