import { act, render, renderHook, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import type { ReactNode } from "react";
import { describe, expect, it } from "vitest";
import type { Book } from "@/lib/types";
import { AddToCartButton } from "./AddToCartButton";
import { CartProvider, formatMoney, useCart, type CartItem } from "./CartProvider";

const wrapper = ({ children }: { children: ReactNode }) => <CartProvider>{children}</CartProvider>;

const item: CartItem = { slug: "group-home-startup", title: "Group Home Startup", price_cents: 59700, currency: "USD", formatted_price: "$597.00", cover: null };

const book: Book = {
  id: 1,
  slug: "hospice-care-agency-startup",
  title: "Hospice Care Agency Startup",
  subtitle: null,
  short_description: null,
  sku: "HC-015",
  format: "PDF download",
  cover: null,
  price: { amount_cents: 99700, regular_cents: 99700, currency: "USD", formatted: "$997.00", formatted_regular: "$997.00", on_sale: false, is_free: false },
  categories: [],
  is_featured: true,
  is_purchasable: true,
  external_purchase_url: null,
  published_at: null,
  updated_at: null,
};

describe("CartProvider", () => {
  it("adds each product once and totals the cart", () => {
    const { result } = renderHook(() => useCart(), { wrapper });

    act(() => result.current.add(item));
    act(() => result.current.add(item));
    act(() => result.current.add({ ...item, slug: "janitorial-startup", price_cents: 9700 }));

    expect(result.current.count).toBe(2);
    expect(result.current.totalCents).toBe(69400);
    expect(result.current.has("group-home-startup")).toBe(true);
  });

  it("persists to localStorage and restores on mount", () => {
    const first = renderHook(() => useCart(), { wrapper });
    act(() => first.result.current.add(item));
    first.unmount();

    const second = renderHook(() => useCart(), { wrapper });
    expect(second.result.current.items).toEqual([item]);

    act(() => second.result.current.remove("group-home-startup"));
    expect(JSON.parse(window.localStorage.getItem("csinc.cart.v1") ?? "[]")).toEqual([]);
  });

  it("recovers from corrupted storage", () => {
    window.localStorage.setItem("csinc.cart.v1", "{not json");
    const { result } = renderHook(() => useCart(), { wrapper });

    expect(result.current.items).toEqual([]);
  });

  it("formats currency", () => {
    expect(formatMoney(19700)).toBe("$197.00");
  });
});

describe("AddToCartButton", () => {
  it("adds the product and switches to a checkout link", async () => {
    render(<AddToCartButton book={book} />, { wrapper });

    await userEvent.click(await screen.findByRole("button", { name: /add to cart — \$997\.00/i }));

    expect(screen.getByRole("link", { name: /view cart/i })).toHaveAttribute("href", "/cart");
    expect(screen.getByRole("status")).toHaveTextContent("Added to cart");
  });

  it("renders nothing for unpriced products", () => {
    const { container } = render(<AddToCartButton book={{ ...book, price: null }} />, { wrapper });

    expect(container).toBeEmptyDOMElement();
  });
});
