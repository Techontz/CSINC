import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { isExternal, SmartLink } from "./SmartLink";

describe("SmartLink", () => {
  it("detects external targets", () => {
    expect(isExternal("https://stripe.com")).toBe(true);
    expect(isExternal("mailto:Info@CSinc91.com")).toBe(true);
    expect(isExternal("/products")).toBe(false);
  });

  it("opens external web links in a new tab safely", () => {
    render(<SmartLink href="https://www.linkedin.com">LinkedIn</SmartLink>);
    const link = screen.getByRole("link", { name: "LinkedIn" });

    expect(link).toHaveAttribute("target", "_blank");
    expect(link).toHaveAttribute("rel", "noopener noreferrer");
  });

  it("keeps mailto links in the same tab", () => {
    render(<SmartLink href="mailto:Info@CSinc91.com">Email</SmartLink>);

    expect(screen.getByRole("link", { name: "Email" })).not.toHaveAttribute("target");
  });
});
