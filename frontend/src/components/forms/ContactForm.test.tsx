import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { afterEach, describe, expect, it, vi } from "vitest";
import { ContactForm } from "./ContactForm";

const options = {
  industries: ["Healthcare-related"],
  growth_stages: ["Startup", "Scaling"],
  challenges: ["Strategy Development"],
  goals: ["Revenue"],
  services_of_interest: ["Strategic Planning"],
  preferred_services: ["Business Formation"],
};

afterEach(() => vi.unstubAllGlobals());

describe("ContactForm", () => {
  it("shows field errors returned by the API", async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(JSON.stringify({ message: "Invalid", errors: { email: ["The email field must be a valid email address."], "address.city": ["The city field is required."] } }), { status: 422 }),
    );
    vi.stubGlobal("fetch", fetchMock);

    render(<ContactForm options={options} />);
    await userEvent.click(screen.getByRole("button", { name: /submit inquiry/i }));

    expect(await screen.findByText("The email field must be a valid email address.")).toBeInTheDocument();
    expect(screen.getByText("The city field is required.")).toBeInTheDocument();
    expect(screen.getByLabelText(/^email/i)).toHaveAttribute("aria-invalid", "true");

    const body = JSON.parse(fetchMock.mock.calls[0][1].body);
    expect(fetchMock.mock.calls[0][0]).toBe("/api/contact");
    expect(body).toHaveProperty("started_at");
    expect(body.website).toBe("");
  });

  it("sends the structured payload and shows the success state", async () => {
    const fetchMock = vi.fn().mockResolvedValue(new Response(JSON.stringify({ message: "Thank you — received." }), { status: 201 }));
    vi.stubGlobal("fetch", fetchMock);
    window.scrollTo = vi.fn();

    render(<ContactForm options={options} />);
    await userEvent.type(screen.getByLabelText(/first name/i), "Ada");
    await userEvent.click(screen.getByLabelText("Scaling"));
    await userEvent.click(screen.getByLabelText("Strategy Development"));
    await userEvent.click(screen.getByRole("checkbox", { name: /i agree/i }));
    await userEvent.click(screen.getByRole("button", { name: /submit inquiry/i }));

    await waitFor(() => expect(screen.getByRole("status")).toHaveTextContent("Thank you — received."));
    const body = JSON.parse(fetchMock.mock.calls[0][1].body);
    expect(body).toMatchObject({ first_name: "Ada", growth_stage: "Scaling", challenges: ["Strategy Development"], consent: true });
  });

  it("explains rate limiting", async () => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue(new Response("{}", { status: 429 })));

    render(<ContactForm options={options} />);
    await userEvent.click(screen.getByRole("button", { name: /submit inquiry/i }));

    expect(await screen.findByRole("alert")).toHaveTextContent(/wait a minute/i);
  });
});
