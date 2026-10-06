"use client";

import Link from "next/link";
import { usePathname, useRouter } from "next/navigation";
import { useEffect, useRef, useState } from "react";
import { cn } from "@/lib/cn";
import type { NavigationLink, SiteData } from "@/lib/types";
import { useCart } from "@/components/cart/CartProvider";
import { ArrowUpRight, Bag, ChevronDown, Close, Menu, Search } from "@/components/ui/Icons";
import { SmartLink } from "@/components/ui/SmartLink";
import { Logo } from "./Logo";
import { MobileNav } from "./MobileNav";

const CONTACT_PATH = "/contact-us";

function isActive(pathname: string, href: string): boolean {
  const path = href.split("#")[0];
  return path !== "/" && (pathname === path || pathname.startsWith(`${path}/`));
}

export function SiteHeader({ site }: { site: SiteData }) {
  const pathname = usePathname();
  const { count, ready } = useCart();
  const [openMenu, setOpenMenu] = useState<string | null>(null);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const router = useRouter();
  const [hidden, setHidden] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const closeTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
  const lastY = useRef(0);

  const items = site.navigation.header.filter((item) => item.url !== CONTACT_PATH);
  const contact = site.navigation.header.find((item) => item.url === CONTACT_PATH);

  // Close menus on navigation.
  useEffect(() => {
    // eslint-disable-next-line react-hooks/set-state-in-effect -- reset UI state when the route changes
    setOpenMenu(null);
    setMobileOpen(false);
    setSearchOpen(false);
  }, [pathname]);

  // Hide on scroll down, reveal on scroll up.
  useEffect(() => {
    const onScroll = () => {
      const y = window.scrollY;
      setScrolled(y > 8);
      setHidden(y > 160 && y > lastY.current && !openMenu && !searchOpen);
      lastY.current = y;
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, [openMenu, searchOpen]);

  useEffect(() => {
    const onKey = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setOpenMenu(null);
        setSearchOpen(false);
      }
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
  }, []);

  const open = (label: string) => {
    if (closeTimer.current) clearTimeout(closeTimer.current);
    setOpenMenu(label);
  };
  const scheduleClose = () => {
    closeTimer.current = setTimeout(() => setOpenMenu(null), 140);
  };

  const active = items.find((item) => item.label === openMenu && item.children.length > 0);

  return (
    <>
      <header
        className={cn(
          "fixed inset-x-0 top-0 z-50 transition-transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]",
          hidden && !mobileOpen ? "-translate-y-full" : "translate-y-0",
        )}
        onMouseLeave={scheduleClose}
      >
        <div aria-hidden="true" className="h-[5px] bg-gradient-to-r from-navy-soft via-[#2f5d8a] to-gold" />
        <div className={cn("bg-navy transition-shadow duration-300", (scrolled || openMenu) && "shadow-[0_1px_0_rgba(255,255,255,0.06)]")}>
          <div className="container-site flex h-[4.1875rem] items-center justify-between gap-6 lg:h-[4.375rem]">
            <Logo image={site.logo_inverse} inverse name={site.name} className="shrink-0" />

            <div className="flex h-full items-center">
              <nav aria-label="Main" className="hidden h-full lg:block">
                <ul className="flex h-full items-center">
                  {items.map((item) => (
                    <NavItem
                      key={item.label}
                      item={item}
                      active={isActive(pathname, item.url) || item.children.some((child) => isActive(pathname, child.url))}
                      expanded={openMenu === item.label}
                      onOpen={() => open(item.label)}
                      onToggle={() => setOpenMenu(openMenu === item.label ? null : item.label)}
                    />
                  ))}
                </ul>
              </nav>

              {contact && (
                <Link
                  href={contact.url}
                  className="group ml-3 hidden items-center gap-2.5 border border-white px-[0.9375rem] py-2.5 text-xs font-medium uppercase leading-none tracking-[1.5px] text-white transition-colors hover:bg-white hover:text-navy lg:inline-flex"
                >
                  {contact.label}
                  <ArrowUpRight size={14} strokeWidth={2.5} className="arrow-shift" />
                </Link>
              )}
              <button
                type="button"
                onClick={() => {
                  setOpenMenu(null);
                  setSearchOpen((value) => !value);
                }}
                aria-label={searchOpen ? "Close search" : "Search products"}
                aria-expanded={searchOpen}
                aria-controls="site-search"
                className="ml-1 flex h-11 w-11 items-center justify-center text-white transition-colors hover:text-gold-light lg:ml-2"
              >
                {searchOpen ? <Close size={20} /> : <Search size={20} />}
              </button>
              <Link
                href="/cart"
                className="relative flex h-11 w-11 items-center justify-center text-white transition-colors hover:text-gold-light"
                aria-label={`Cart${ready && count ? `, ${count} item${count > 1 ? "s" : ""}` : ""}`}
              >
                <Bag />
                {ready && count > 0 && (
                  <span className="absolute right-1 top-1.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-gold px-1 text-[10px] font-bold text-navy-deep">
                    {count}
                  </span>
                )}
              </Link>
              <button
                type="button"
                className="flex h-11 w-11 items-center justify-center text-white lg:hidden"
                aria-label="Open menu"
                aria-expanded={mobileOpen}
                aria-controls="mobile-nav"
                onClick={() => setMobileOpen(true)}
              >
                <Menu />
              </button>
            </div>
          </div>
        </div>

        {/* Search panel */}
        <div
          id="site-search"
          className={cn(
            "absolute inset-x-0 top-full bg-white shadow-[0_30px_60px_-30px_rgba(0,28,53,0.35)] transition-[opacity,transform] duration-300",
            searchOpen ? "visible translate-y-0 opacity-100" : "invisible -translate-y-2 opacity-0",
          )}
        >
          <form
            role="search"
            className="container-site flex items-center gap-4 py-8 md:py-12"
            onSubmit={(event) => {
              event.preventDefault();
              const query = new FormData(event.currentTarget).get("search")?.toString().trim();
              setSearchOpen(false);
              router.push(query ? `/products?search=${encodeURIComponent(query)}` : "/products");
            }}
          >
            <label htmlFor="site-search-input" className="sr-only">Search startup guides</label>
            <Search size={26} className="shrink-0 text-gold" />
            <input
              id="site-search-input"
              name="search"
              type="search"
              maxLength={100}
              placeholder="Search startup guides, e.g. “home health”"
              autoFocus={searchOpen}
              key={searchOpen ? "open" : "closed"}
              className="w-full border-0 bg-transparent font-serif text-2xl text-navy placeholder:text-navy/35 focus:outline-none focus:ring-0 md:text-[2.5rem]"
            />
            <button type="submit" className="hidden shrink-0 items-center gap-2 border border-navy px-5 py-3 text-xs font-bold uppercase tracking-[1.5px] text-navy transition-colors hover:bg-navy hover:text-white sm:inline-flex">
              Search <ArrowUpRight size={14} strokeWidth={2.5} />
            </button>
          </form>
        </div>

        {/* Desktop mega menu */}
        <div
          className={cn(
            "absolute inset-x-0 top-full hidden origin-top bg-white shadow-[0_30px_60px_-30px_rgba(0,28,53,0.35)] transition-[opacity,transform] duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] lg:block",
            active ? "visible translate-y-0 opacity-100" : "invisible -translate-y-2 opacity-0",
          )}
          onMouseEnter={() => active && open(active.label)}
        >
          {active && (
            <div className="container-site grid grid-cols-12 gap-10 py-14" id={`mega-${active.label}`}>
              <div className="col-span-4 border-r border-line pr-10">
                <p className="eyebrow text-gold-ink">{active.label}</p>
                <p className="mt-5 font-serif text-[1.75rem] leading-tight text-navy">{active.description}</p>
                <Link href={active.url} className="group mt-8 inline-flex items-center gap-2 text-[0.75rem] font-semibold uppercase tracking-[0.14em] text-navy">
                  <span className="link-underline pb-0.5">Explore {active.label.toLowerCase()}</span>
                  <ArrowUpRight size={14} className="arrow-shift text-gold" />
                </Link>
              </div>
              <ul className="col-span-8 grid grid-cols-2 gap-x-10 gap-y-2">
                {active.children.map((child) => (
                  <li key={child.url}>
                    <SmartLink href={child.url} newTab={child.new_tab} className="group flex items-start justify-between gap-6 border-b border-line py-5">
                      <span>
                        <span className="block text-[1.05rem] font-medium text-navy transition-colors group-hover:text-gold-ink">{child.label}</span>
                        {child.description && <span className="mt-1.5 block text-sm leading-relaxed text-muted">{child.description}</span>}
                      </span>
                      <ArrowUpRight size={16} className="arrow-shift mt-1 shrink-0 text-navy/40 group-hover:text-gold-ink" />
                    </SmartLink>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </div>
      </header>

      {/* Dim page content while the mega menu is open */}
      <div
        aria-hidden="true"
        className={cn(
          "fixed inset-0 z-40 hidden bg-navy-deep/30 backdrop-blur-[2px] transition-opacity duration-300 lg:block",
          active || searchOpen ? "opacity-100" : "pointer-events-none opacity-0",
        )}
        onClick={() => {
          setOpenMenu(null);
          setSearchOpen(false);
        }}
      />

      <MobileNav site={site} open={mobileOpen} onClose={() => setMobileOpen(false)} />
    </>
  );
}

function NavItem({
  item,
  active,
  expanded,
  onOpen,
  onToggle,
}: {
  item: NavigationLink;
  active: boolean;
  expanded: boolean;
  onOpen: () => void;
  onToggle: () => void;
}) {
  const hasChildren = item.children.length > 0;
  const label = (
    <span className={cn("relative py-1", active && "after:absolute after:inset-x-0 after:-bottom-1 after:h-[2px] after:bg-gold-light")}>{item.label}</span>
  );
  const base = "flex h-full items-center pl-[0.875rem] pr-1 pt-[2px] text-sm font-medium uppercase leading-none tracking-[1.5px] transition-colors";

  return (
    <li className="flex h-full items-center" onMouseEnter={hasChildren ? onOpen : undefined}>
      <SmartLink
        href={item.url}
        newTab={item.new_tab}
        className={cn(base, expanded || active ? "text-gold-light" : "text-white hover:text-gold-light")}
      >
        {label}
      </SmartLink>
      {hasChildren && (
        <button
          type="button"
          onClick={onToggle}
          aria-expanded={expanded}
          aria-controls={`mega-${item.label}`}
          aria-label={`Show ${item.label} menu`}
          className={cn("mr-2 flex h-8 w-6 items-center justify-center text-white transition-transform hover:text-gold-light", expanded && "rotate-180 text-gold-light")}
        >
          <ChevronDown size={13} strokeWidth={2.25} />
        </button>
      )}
    </li>
  );
}
