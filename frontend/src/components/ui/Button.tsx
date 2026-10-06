import type { ReactNode } from "react";
import { cn } from "@/lib/cn";
import { ArrowUpRight } from "./Icons";
import { SmartLink } from "./SmartLink";

type Variant = "solid" | "outline" | "light" | "outline-light" | "gold";

const variants: Record<Variant, string> = {
  solid: "bg-navy text-white hover:bg-navy-deep",
  outline: "border border-gold text-navy hover:bg-navy hover:border-navy hover:text-white",
  light: "bg-white text-navy hover:bg-gold-light",
  "outline-light": "border border-white/35 text-white hover:border-white hover:bg-white hover:text-navy",
  gold: "bg-gold text-navy-deep hover:bg-gold-light",
};

export const buttonClasses = (variant: Variant = "solid", className?: string) =>
  cn(
    "group inline-flex min-h-[2.875rem] items-center justify-center gap-3 px-[1.125rem] py-3 text-sm font-bold uppercase tracking-[0.1em] transition-colors duration-300",
    "disabled:cursor-not-allowed disabled:opacity-50",
    variants[variant],
    className,
  );

export function ButtonLink({
  href,
  children,
  variant = "solid",
  className,
  icon = true,
}: {
  href: string;
  children: ReactNode;
  variant?: Variant;
  className?: string;
  icon?: boolean;
}) {
  return (
    <SmartLink href={href} className={buttonClasses(variant, className)}>
      <span>{children}</span>
      {icon && <ArrowUpRight size={16} strokeWidth={2.25} className="arrow-shift" />}
    </SmartLink>
  );
}

/** Understated text link with an arrow, used for “See all” style actions. */
export function ArrowLink({
  href,
  children,
  className,
  tone = "dark",
}: {
  href: string;
  children: ReactNode;
  className?: string;
  tone?: "dark" | "light";
}) {
  return (
    <SmartLink
      href={href}
      className={cn(
        "group inline-flex items-center gap-2 text-sm font-bold uppercase tracking-[0.1em]",
        tone === "dark" ? "text-navy" : "text-white",
        className,
      )}
    >
      <span className="link-underline pb-0.5">{children}</span>
      <ArrowUpRight size={16} strokeWidth={2.25} className={cn("arrow-shift", tone === "dark" ? "text-gold-ink" : "text-gold-light")} />
    </SmartLink>
  );
}
