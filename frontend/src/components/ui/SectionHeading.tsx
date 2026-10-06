import type { ReactNode } from "react";
import { cn } from "@/lib/cn";
import { Accent } from "./Accent";
import { Reveal } from "./Reveal";

/**
 * Small uppercase label above headings. `edge` draws a rule running in from
 * the left edge of the viewport, used on page heroes.
 */
export function Eyebrow({ children, tone = "dark", className, edge = false }: { children: ReactNode; tone?: "dark" | "light"; className?: string; edge?: boolean }) {
  return (
    <p className={cn("eyebrow relative flex items-center", tone === "dark" ? "text-gold-ink" : "text-gold-light", className)}>
      {edge && <span aria-hidden="true" className="absolute right-full mr-4 h-[2px] w-[100vw] max-w-[3.125rem] bg-current" />}
      {children}
    </p>
  );
}

export function SectionHeading({
  eyebrow,
  heading,
  intro,
  tone = "dark",
  action,
  className,
  as: Tag = "h2",
  size = "lg",
}: {
  eyebrow?: string | null;
  heading: string;
  intro?: string | null;
  tone?: "dark" | "light";
  action?: ReactNode;
  className?: string;
  as?: "h1" | "h2";
  size?: "lg" | "sm";
}) {
  return (
    <div className={cn("flex flex-col gap-8 md:flex-row md:items-end md:justify-between", className)}>
      <Reveal className="max-w-3xl">
        {eyebrow && <Eyebrow tone={tone} className="mb-4">{eyebrow}</Eyebrow>}
        <Tag className={cn("font-serif", size === "lg" ? "text-h2" : "text-h2s", tone === "dark" ? "text-navy" : "text-white [&_em]:text-gold-light")}>
          <Accent text={heading} />
        </Tag>
        {intro && <p className={cn("mt-5 max-w-2xl text-lead", tone === "dark" ? "text-muted" : "text-white/75")}>{intro}</p>}
      </Reveal>
      {action && <Reveal delay={120} className="shrink-0">{action}</Reveal>}
    </div>
  );
}
