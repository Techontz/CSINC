"use client";

import { useState, type FormEvent } from "react";
import { TextField } from "@/components/forms/Field";
import { buttonClasses } from "@/components/ui/Button";
import { ArrowUpRight, Check } from "@/components/ui/Icons";

export function ResendDownloadsForm() {
  const [status, setStatus] = useState<"idle" | "submitting" | "done" | "error">("idle");
  const [message, setMessage] = useState("");
  const [error, setError] = useState<string | undefined>();

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setStatus("submitting");
    setError(undefined);

    try {
      const response = await fetch("/api/downloads/resend", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({ email: form.get("email"), website: form.get("website") }),
      });
      const body = await response.json().catch(() => ({}));

      if (response.ok) {
        setStatus("done");
        setMessage(body.message);
        return;
      }

      setStatus("error");
      setError(body.errors?.email?.[0]);
      setMessage(response.status === 429 ? "Too many requests. Please wait a minute and try again." : body.message ?? "Something went wrong. Please try again.");
    } catch {
      setStatus("error");
      setMessage("We could not reach the server. Please try again.");
    }
  }

  if (status === "done") {
    return (
      <div role="status" className="bg-paper p-10">
        <span className="flex h-12 w-12 items-center justify-center rounded-full bg-navy text-gold-light"><Check /></span>
        <p className="mt-6 font-serif text-2xl text-navy">Check your inbox</p>
        <p className="mt-3 text-muted">{message}</p>
      </div>
    );
  }

  return (
    <form onSubmit={onSubmit} noValidate className="space-y-8 bg-paper p-8 md:p-10">
      <TextField label="Email used at checkout" name="email" type="email" autoComplete="email" required error={error} />
      <div aria-hidden="true" className="absolute -left-[9999px] h-px w-px overflow-hidden">
        <input name="website" tabIndex={-1} autoComplete="off" />
      </div>
      {status === "error" && !error && <p role="alert" className="text-sm text-red-700">{message}</p>}
      <button type="submit" disabled={status === "submitting"} className={buttonClasses("solid", "w-full")}>
        <span>{status === "submitting" ? "Sending…" : "Email my download links"}</span>
        {status !== "submitting" && <ArrowUpRight size={15} className="arrow-shift" />}
      </button>
    </form>
  );
}
