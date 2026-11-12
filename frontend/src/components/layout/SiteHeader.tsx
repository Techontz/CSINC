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
        <div className={cn("border-b bg-white/95 backdrop-blur-md transition-[border-color,box-shadow] duration-300", scrolled || openMenu ? "border-line shadow-[0_8px_30px_-18px_rgba(0,28,53,0.35)]" : "border-transparent")}>
          <div className="container-site flex h-[5rem] items-center justify-between gap-6 lg:h-[5.75rem]">
            <Logo image={site.logo} name={site.name} className="shrink-0" />

            <div className="flex h-full items-center">
              <nav aria-label="Main" className="hidden h-full lg:block">
                <ul className="flex h-full items-center gap-1">
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
                  className="group ml-5 hidden items-center gap-2 rounded-xl bg-navy px-5 py-3.5 text-base font-semibold leading-none text-white shadow-[0_10px_24px_-12px_rgba(0,41,76,0.7)] transition-colors hover:bg-gold lg:inline-flex"
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
                className="ml-1 flex h-12 w-12 items-center justify-center rounded-full text-navy transition-colors hover:bg-sky lg:ml-3"
              >
                {searchOpen ? <Close size={22} /> : <Search size={22} />}
              </button>
              <Link
                href="/cart"
                className="relative flex h-12 w-12 items-center justify-center rounded-full text-navy transition-colors hover:bg-sky"
                aria-label={`Cart${ready && count ? `, ${count} item${count > 1 ? "s" : ""}` : ""}`}
              >
                <Bag size={22} />
                {ready && count > 0 && (
                  <span className="absolute right-1 top-1.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-gold px-1 text-[10px] font-bold text-white">
                    {count}
                  </span>
                )}
              </Link>
              <button
                type="button"
                className="flex h-12 w-12 items-center justify-center rounded-full text-navy hover:bg-sky lg:hidden"
                aria-label="Open menu"
                aria-expanded={mobileOpen}
                aria-controls="mobile-nav"
                onClick={() => setMobileOpen(true)}
              >
                <Menu size={26} />
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
              className="w-full border-0 bg-transparent text-2xl font-semibold tracking-[-0.02em] text-navy placeholder:text-navy/35 focus:outline-none focus:ring-0 md:text-[2.5rem]"
            />
            <button type="submit" className="hidden shrink-0 items-center gap-2 rounded-xl bg-navy px-5 py-3 text-[0.9375rem] font-semibold text-white transition-colors hover:bg-navy-deep sm:inline-flex">
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
            <div className="container-site grid grid-cols-12 gap-10 py-16" id={`mega-${active.label}`}>
              <div className="col-span-4 border-r border-line pr-10">
                <p className="tag-pill">{active.label}</p>
                <p className="mt-5 font-serif text-[1.75rem] leading-tight tracking-[-0.025em] text-navy">{active.description}</p>
                <Link href={active.url} className="group mt-8 inline-flex items-center gap-2 text-[0.9375rem] font-semibold text-navy">
                  <span className="link-underline pb-0.5">Explore {active.label.toLowerCase()}</span>
                  <ArrowUpRight size={14} className="arrow-shift text-gold" />
                </Link>
              </div>
              <ul className="col-span-8 grid grid-cols-2 gap-x-10 gap-y-2">
                {active.children.map((child) => (
                  <li key={child.url}>
                    <SmartLink href={child.url} newTab={child.new_tab} className="group -mx-4 flex items-start justify-between gap-6 rounded-2xl px-4 py-4 transition-colors hover:bg-sky">
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
    <span className={cn("relative py-1", active && "after:absolute after:inset-x-0 after:-bottom-1 after:h-[2px] after:rounded-full after:bg-gold")}>{item.label}</span>
  );
  const base = "flex h-full items-center pl-4 pr-1 text-[1.0625rem] font-semibold leading-none tracking-[-0.01em] transition-colors";

  return (
    <li className="flex h-full items-center" onMouseEnter={hasChildren ? onOpen : undefined}>
      <SmartLink
        href={item.url}
        newTab={item.new_tab}
        className={cn(base, expanded || active ? "text-gold-ink" : "text-navy hover:text-gold-ink")}
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
          className={cn("mr-1 flex h-8 w-6 items-center justify-center text-navy transition-transform hover:text-gold-ink", expanded && "rotate-180 text-gold-ink")}
        >
          <ChevronDown size={15} strokeWidth={2.25} />
        </button>
      )}
    </li>
  );
}
