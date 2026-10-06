"use client";

import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from "react";

export type CartItem = {
  slug: string;
  title: string;
  price_cents: number;
  currency: string;
  formatted_price: string;
  cover: string | null;
};

type CartContextValue = {
  items: CartItem[];
  count: number;
  totalCents: number;
  ready: boolean;
  add: (item: CartItem) => void;
  remove: (slug: string) => void;
  clear: () => void;
  has: (slug: string) => boolean;
};

const STORAGE_KEY = "csinc.cart.v1";
const CartContext = createContext<CartContextValue | null>(null);

function readStorage(): CartItem[] {
  try {
    const parsed = JSON.parse(window.localStorage.getItem(STORAGE_KEY) ?? "[]");
    return Array.isArray(parsed) ? parsed.filter((item) => typeof item?.slug === "string") : [];
  } catch {
    return [];
  }
}

/**
 * Digital products are single-licence purchases, so the cart holds each
 * product at most once. State persists in localStorage between visits.
 */
export function CartProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<CartItem[]>([]);
  const [ready, setReady] = useState(false);

  useEffect(() => {
    // eslint-disable-next-line react-hooks/set-state-in-effect -- hydrate from storage after mount to avoid SSR mismatch
    setItems(readStorage());
    setReady(true);

    const sync = (event: StorageEvent) => {
      if (event.key === STORAGE_KEY) {
        setItems(readStorage());
      }
    };
    window.addEventListener("storage", sync);
    return () => window.removeEventListener("storage", sync);
  }, []);

  const persist = useCallback((next: CartItem[]) => {
    setItems(next);
    try {
      window.localStorage.setItem(STORAGE_KEY, JSON.stringify(next));
    } catch {
      // Storage may be unavailable (private mode); the cart still works for this visit.
    }
  }, []);

  const value = useMemo<CartContextValue>(
    () => ({
      items,
      ready,
      count: items.length,
      totalCents: items.reduce((sum, item) => sum + item.price_cents, 0),
      add: (item) => persist([...items.filter((existing) => existing.slug !== item.slug), item]),
      remove: (slug) => persist(items.filter((item) => item.slug !== slug)),
      clear: () => persist([]),
      has: (slug) => items.some((item) => item.slug === slug),
    }),
    [items, ready, persist],
  );

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart(): CartContextValue {
  const context = useContext(CartContext);
  if (!context) {
    throw new Error("useCart must be used within CartProvider");
  }
  return context;
}

export function formatMoney(cents: number, currency = "USD"): string {
  return new Intl.NumberFormat("en-US", { style: "currency", currency }).format(cents / 100);
}
