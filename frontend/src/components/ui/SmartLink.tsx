import Link from "next/link";
import type { AnchorHTMLAttributes, ReactNode } from "react";

type Props = AnchorHTMLAttributes<HTMLAnchorElement> & { href: string; children: ReactNode; newTab?: boolean };

export function isExternal(href: string): boolean {
  return /^(https?:)?\/\//.test(href) || href.startsWith("mailto:") || href.startsWith("tel:");
}

/** Uses next/link for internal routes and a plain anchor for external targets. */
export function SmartLink({ href, children, newTab, ...rest }: Props) {
  if (isExternal(href)) {
    const opensNewTab = newTab ?? /^https?:/.test(href);
    return (
      <a href={href} {...(opensNewTab ? { target: "_blank", rel: "noopener noreferrer" } : {})} {...rest}>
        {children}
      </a>
    );
  }

  return (
    <Link href={href} {...(newTab ? { target: "_blank", rel: "noopener" } : {})} {...rest}>
      {children}
    </Link>
  );
}
