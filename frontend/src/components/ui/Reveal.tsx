"use client";

import { createElement, useEffect, useState, type CSSProperties, type ReactNode } from "react";
import { cn } from "@/lib/cn";

type Props = {
  children: ReactNode;
  /** Render visible immediately (above-the-fold content must not wait for JavaScript). */
  immediate?: boolean;
  as?: "div" | "section" | "li" | "span" | "figure" | "article" | "header";
  className?: string;
  delay?: number;
  variant?: "fade" | "mask";
};

/**
 * Reveals content as it scrolls into view. Pure CSS transitions toggled by a
 * single IntersectionObserver; disabled automatically for reduced motion.
 */
export function Reveal({ children, as = "div", className, delay = 0, variant = "fade", immediate = false }: Props) {
  const [element, setElement] = useState<HTMLElement | null>(null);

  useEffect(() => {
    if (!element || immediate) {
      return;
    }

    if (typeof IntersectionObserver === "undefined" || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
      element.classList.add("is-visible");
      return;
    }

    // A fully clip-path'd element never reports as intersecting, so mask
    // reveals observe their (unclipped) parent instead.
    const target = variant === "mask" ? (element.parentElement ?? element) : element;

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
          element.classList.add("is-visible");
          observer.disconnect();
        }
      },
      { rootMargin: "0px 0px -8% 0px", threshold: 0.08 },
    );

    observer.observe(target);
    return () => observer.disconnect();
  }, [element, variant, immediate]);

  return createElement(
    as,
    {
      ref: setElement,
      className: cn(!immediate && (variant === "mask" ? "reveal-mask" : "reveal"), className),
      style: { "--reveal-delay": `${delay}ms` } as CSSProperties,
    },
    children,
  );
}
