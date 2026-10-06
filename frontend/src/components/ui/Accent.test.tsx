import { render } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { Accent, stripAccent } from "./Accent";

describe("Accent", () => {
  it("renders asterisk-wrapped words as italic accents", () => {
    const { container } = render(<h1><Accent text="Transforming ideas into *scalable* business success" /></h1>);

    expect(container.querySelector("em.accent")?.textContent).toBe("scalable");
    expect(container.textContent).toBe("Transforming ideas into scalable business success");
  });

  it("escapes markup instead of injecting HTML", () => {
    const { container } = render(<p><Accent text={'<img src=x onerror=alert(1)> *safe*'} /></p>);

    expect(container.querySelector("img")).toBeNull();
    expect(container.textContent).toContain("<img src=x onerror=alert(1)>");
  });

  it("strips accent markers for plain-text contexts", () => {
    expect(stripAccent("Our four *core* services")).toBe("Our four core services");
    expect(stripAccent(null)).toBe("");
  });
});
