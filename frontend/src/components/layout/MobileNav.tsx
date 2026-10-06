"use client";

import Link from "next/link";
import { useEffect, useRef, useState } from "react";
import { cn } from "@/lib/cn";
import type { SiteData } from "@/lib/types";
import { ArrowUpRight, Close, Plus, SocialIcon } from "@/components/ui/Icons";
import { SmartLink } from "@/components/ui/SmartLink";
import { Logo } from "./Logo";

/**
 * Full-screen mobile navigation: large serif links with expandable sections,
 * designed for thumbs rather than shrunk from the desktop menu.
 */
export function MobileNav({ site, open, onClose }: { site: SiteData; open: boolean; onClose: () => void }) {
  const [expanded, setExpanded] = useState<string | null>(null);
  const closeButton = useRef<HTMLButtonElement>(null);

  useEffect(() => {
    if (!open) {
      return;
    }
    document.documentElement.style.overflow = "hidden";
    closeButton.current?.focus();
    const onKey = (event: KeyboardEvent) => event.key === "Escape" && onClose();
    window.addEventListener("keydown", onKey);
    return () => {
      document.documentElement.style.overflow = "";
      window.removeEventListener("keydown", onKey);
    };
  }, [open, onClose]);

  const { contact } = site;

  return (
    <div
      id="mobile-nav"
      role="dialog"
      aria-modal="true"
      aria-label="Menu"
      className={cn(
        "fixed inset-0 z-[60] flex flex-col bg-navy-deep text-white transition-[opacity,visibility] duration-300 lg:hidden",
        open ? "visible opacity-100" : "invisible opacity-0",
      )}
    >
      <div className="container-site flex h-[4.5rem] shrink-0 items-center justify-between">
        <Logo image={site.logo_inverse} inverse name={site.name} />
        <button ref={closeButton} type="button" onClick={onClose} className="flex h-11 w-11 items-center justify-center" aria-label="Close menu">
          <Close />
        </button>
      </div>

      <nav aria-label="Mobile" className="container-site flex-1 overflow-y-auto pb-10 pt-6">
        <ul>
          {site.navigation.header.map((item, index) => {
            const isOpen = expanded === item.label;
            return (
              <li
                key={item.label}
                className={cn("border-b border-white/10 transition-[opacity,transform] duration-500", open ? "translate-y-0 opacity-100" : "translate-y-3 opacity-0")}
                style={{ transitionDelay: open ? `${80 + index * 50}ms` : "0ms" }}
              >
                <div className="flex items-center justify-between">
                  <SmartLink href={item.url} newTab={item.new_tab} className="flex-1 py-5 font-serif text-[2rem] leading-none" onClick={onClose}>
                    {item.label}
                  </SmartLink>
                  {item.children.length > 0 && (
                    <button
                      type="button"
                      onClick={() => setExpanded(isOpen ? null : item.label)}
                      aria-expanded={isOpen}
                      aria-label={`${isOpen ? "Hide" : "Show"} ${item.label} links`}
                      className="flex h-12 w-12 items-center justify-center text-gold-light"
                    >
                      <Plus className={cn("transition-transform duration-300", isOpen && "rotate-45")} />
                    </button>
                  )}
                </div>
                {item.children.length > 0 && (
                  <div className={cn("grid transition-[grid-template-rows] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]", isOpen ? "grid-rows-[1fr]" : "grid-rows-[0fr]")}>
                    <ul className="overflow-hidden">
                      {item.children.map((child) => (
                        <li key={child.url}>
                          <SmartLink
                            href={child.url}
                            newTab={child.new_tab}
                            onClick={onClose}
                            tabIndex={isOpen ? 0 : -1}
                            className="flex items-center justify-between gap-4 py-3 pl-1 text-[1.02rem] text-white/75 last:pb-6"
                          >
                            {child.label}
                            <ArrowUpRight size={15} className="text-gold-light" />
                          </SmartLink>
                        </li>
                      ))}
                    </ul>
                  </div>
                )}
              </li>
            );
          })}
        </ul>

        <div className="mt-10 space-y-2 text-sm text-white/65">
          <p className="eyebrow mb-4 text-gold-light">Get in touch</p>
          <a href={`mailto:${contact.email}`} className="block text-lg text-white">{contact.email}</a>
          {contact.phone && <a href={`tel:${contact.phone.replace(/[^0-9+]/g, "")}`} className="block text-lg text-white">{contact.phone}</a>}
          <p>{[contact.address.line_1, contact.address.city, contact.address.region, contact.address.postal_code].filter(Boolean).join(", ")}</p>
        </div>

        <div className="mt-8 flex gap-3">
          <Link href="/cart" onClick={onClose} className="flex-1 border border-white/25 py-3.5 text-center text-xs font-semibold uppercase tracking-[0.16em]">
            Cart
          </Link>
          <Link href="/downloads" onClick={onClose} className="flex-1 border border-white/25 py-3.5 text-center text-xs font-semibold uppercase tracking-[0.16em]">
            My downloads
          </Link>
        </div>

        {site.social.length > 0 && (
          <div className="mt-8 flex gap-4">
            {site.social.map((link) => (
              <a key={link.url} href={link.url} target="_blank" rel="noopener noreferrer" aria-label={link.platform} className="text-white/70 hover:text-white">
                <SocialIcon platform={link.platform} />
              </a>
            ))}
          </div>
        )}
      </nav>
    </div>
  );
}
