"use client";

import { useSearchParams } from "next/navigation";
import { useRef, useState, type FormEvent } from "react";
import type { ConsultationOptions } from "@/lib/types";
import { buttonClasses } from "@/components/ui/Button";
import { ArrowUpRight, Check } from "@/components/ui/Icons";
import { ChoicePills, SelectField, TextAreaField, TextField } from "./Field";

type Errors = Record<string, string>;
type Status = "idle" | "submitting" | "success" | "error";

function today(): string {
  const now = new Date();
  return new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

/** Converts Laravel's flat validation keys (address.city, challenges.0) into field names. */
function mapErrors(errors: Record<string, string[]>): Errors {
  const mapped: Errors = {};
  Object.entries(errors).forEach(([key, messages]) => {
    const field = key.replace(/\.(\d+)$/, "").replace(/^address\.(.+)$/, "address_$1");
    mapped[field] ??= messages[0];
  });
  return mapped;
}

export function ContactForm({ options }: { options: ConsultationOptions }) {
  const searchParams = useSearchParams();
  const product = searchParams.get("product") ?? "";
  const [status, setStatus] = useState<Status>("idle");
  const [errors, setErrors] = useState<Errors>({});
  const [message, setMessage] = useState("");
  const startedAt = useRef<number>(0);
  const formRef = useRef<HTMLFormElement>(null);

  const markStarted = () => {
    if (!startedAt.current) {
      startedAt.current = Date.now();
    }
  };

  async function onSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setStatus("submitting");
    setErrors({});

    const payload = {
      first_name: form.get("first_name"),
      last_name: form.get("last_name"),
      email: form.get("email"),
      phone: form.get("phone"),
      company: form.get("company") || null,
      address: {
        street: form.get("address_street") || null,
        line_2: form.get("address_line_2") || null,
        city: form.get("address_city"),
        region: form.get("address_region") || null,
        postal_code: form.get("address_postal_code") || null,
        country: form.get("address_country"),
      },
      industry: form.get("industry") || null,
      growth_stage: form.get("growth_stage"),
      challenges: form.getAll("challenges"),
      primary_goal: form.get("primary_goal") || null,
      message: form.get("message"),
      topic: form.get("topic") || null,
      preferred_start_date: form.get("preferred_start_date"),
      service_of_interest: form.get("service_of_interest") || null,
      preferred_service: form.get("preferred_service") || null,
      product: product || null,
      signature: form.get("signature"),
      consent: form.get("consent") === "on",
      website: form.get("website"),
      started_at: startedAt.current || Date.now(),
    };

    try {
      const response = await fetch("/api/contact", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify(payload),
      });
      const body = await response.json().catch(() => ({}));

      if (response.ok) {
        setStatus("success");
        setMessage(body.message ?? "Thank you. Your inquiry has been received.");
        formRef.current?.reset();
        window.scrollTo({ top: (formRef.current?.getBoundingClientRect().top ?? 0) + window.scrollY - 140 });
        return;
      }

      if (response.status === 422 && body.errors) {
        const mapped = mapErrors(body.errors);
        setErrors(mapped);
        setMessage(mapped.form ?? "Please review the highlighted fields.");
        setStatus("error");
        const first = Object.keys(mapped)[0];
        document.getElementsByName(first)[0]?.focus();
        return;
      }

      setStatus("error");
      setMessage(response.status === 429 ? "You have sent several messages in a short time. Please wait a minute and try again." : "We could not send your message. Please try again, or email us directly.");
    } catch {
      setStatus("error");
      setMessage("We could not reach the server. Please check your connection and try again.");
    }
  }

  if (status === "success") {
    return (
      <div role="status" className="rounded-[2rem] bg-white p-10 shadow-[0_40px_80px_-50px_rgba(0,41,76,0.45)] ring-1 ring-navy/[0.06] md:p-14">
        <span className="flex h-16 w-16 items-center justify-center rounded-2xl bg-gold text-white shadow-[0_12px_28px_-12px_rgba(185,133,44,0.9)]">
          <Check size={26} />
        </span>
        <h3 className="mt-8 text-[2rem] font-bold tracking-[-0.03em] text-navy">Message received</h3>
        <p className="mt-4 max-w-lg text-lead text-muted">{message}</p>
        <button type="button" onClick={() => setStatus("idle")} className={buttonClasses("outline", "mt-10")}>
          Send another message
        </button>
      </div>
    );
  }

  return (
    <form
      ref={formRef}
      onSubmit={onSubmit}
      onFocus={markStarted}
      noValidate
      className="space-y-12 rounded-[2rem] bg-white p-6 shadow-[0_1px_0_rgba(0,28,53,0.04),0_2px_6px_-2px_rgba(0,28,53,0.08),0_40px_80px_-50px_rgba(0,41,76,0.45)] ring-1 ring-navy/[0.06] sm:p-10 md:space-y-14 md:p-12"
      aria-describedby={status === "error" ? "form-status" : undefined}
    >
      {product && (
        <p className="rounded-xl border-l-4 border-gold bg-paper px-5 py-4 text-sm text-navy">
          Inquiry about: <strong className="font-semibold">{product}</strong>
        </p>
      )}

      <FormSection number="01" title="About you">
        <TextField label="First name" name="first_name" autoComplete="given-name" required error={errors.first_name} />
        <TextField label="Last name" name="last_name" autoComplete="family-name" required error={errors.last_name} />
        <TextField label="Email" name="email" type="email" autoComplete="email" required error={errors.email} hint="Share your email to stay updated on opportunities." />
        <TextField label="Phone" name="phone" type="tel" autoComplete="tel" required error={errors.phone} hint="Reach out via phone for immediate assistance." />
        <TextField label="Company (optional)" name="company" autoComplete="organization" error={errors.company} className="md:col-span-2" />
      </FormSection>

      <FormSection number="02" title="Where you’re based">
        <TextField label="Street address" name="address_street" autoComplete="address-line1" error={errors.address_street} className="md:col-span-2" />
        <TextField label="Address line 2" name="address_line_2" autoComplete="address-line2" error={errors.address_line_2} className="md:col-span-2" />
        <TextField label="City" name="address_city" autoComplete="address-level2" required error={errors.address_city} />
        <TextField label="State / region / province" name="address_region" autoComplete="address-level1" error={errors.address_region} />
        <TextField label="Postal code" name="address_postal_code" autoComplete="postal-code" error={errors.address_postal_code} />
        <TextField label="Country" name="address_country" autoComplete="country-name" defaultValue="United States" required error={errors.address_country} />
      </FormSection>

      <FormSection number="03" title="Your business">
        <SelectField label="What industry best describes your business?" name="industry" options={options.industries} error={errors.industry} className="md:col-span-2" />
        <div className="md:col-span-2">
          <ChoicePills legend="How would you describe your business growth stage?" name="growth_stage" type="radio" options={options.growth_stages} required error={errors.growth_stage} />
        </div>
        <div className="md:col-span-2">
          <ChoicePills legend="I’m facing challenges in" name="challenges" type="checkbox" options={options.challenges} required error={errors.challenges} />
        </div>
        <SelectField label="My primary goal is to improve" name="primary_goal" options={options.goals} error={errors.primary_goal} className="md:col-span-2" />
      </FormSection>

      <FormSection number="04" title="Your request">
        <TextAreaField label="Describe your consulting request in detail" name="message" required error={errors.message} className="md:col-span-2" />
        <TextField label="What is your topic of interest?" name="topic" defaultValue={product} error={errors.topic} className="md:col-span-2" />
        <TextField label="When would you like to begin?" name="preferred_start_date" type="date" min={today()} required error={errors.preferred_start_date} />
        <SelectField label="Which of these services interest you?" name="service_of_interest" options={options.services_of_interest} error={errors.service_of_interest} />
        <SelectField label="Preferred engagement" name="preferred_service" options={options.preferred_services} error={errors.preferred_service} className="md:col-span-2" />
      </FormSection>

      <FormSection number="05" title="Confirm">
        <TextField
          label="Your signature"
          name="signature"
          autoComplete="name"
          required
          error={errors.signature}
          hint="Type your full name. Your signature confirms your interest and commitment to growth."
          className="md:col-span-2"
        />
        <label className="flex cursor-pointer items-start gap-4 rounded-2xl bg-paper p-5 md:col-span-2">
          <input type="checkbox" name="consent" className="mt-1 h-5 w-5 shrink-0 accent-navy" aria-invalid={Boolean(errors.consent)} />
          <span className="text-sm leading-relaxed text-muted">
            I agree that CSinc91 may store the details I have submitted and contact me about my inquiry.
            {errors.consent && <span role="alert" className="mt-1 block text-red-700">{errors.consent}</span>}
          </span>
        </label>
      </FormSection>

      {/* Honeypot: hidden from people, irresistible to bots. */}
      <div aria-hidden="true" className="absolute -left-[9999px] h-px w-px overflow-hidden">
        <label htmlFor="website">Website</label>
        <input id="website" name="website" type="text" tabIndex={-1} autoComplete="off" />
      </div>

      <div className="-mx-6 -mb-6 flex flex-col gap-6 rounded-b-[2rem] border-t border-line bg-paper/70 px-6 py-7 sm:-mx-10 sm:-mb-10 sm:flex-row sm:items-center sm:justify-between sm:px-10 md:-mx-12 md:-mb-12 md:px-12">
        <p id="form-status" role={status === "error" ? "alert" : undefined} className={status === "error" ? "text-sm text-red-700" : "flex items-center gap-2.5 text-sm text-muted"}>
          {status !== "error" && <span aria-hidden="true" className="h-2 w-2 shrink-0 rounded-full bg-gold shadow-[0_0_0_4px_rgba(185,133,44,0.18)]" />}
          {status === "error" ? message : "We respond to every inquiry, usually within one business day."}
        </p>
        <button type="submit" disabled={status === "submitting"} className={buttonClasses("solid", "shrink-0 px-7")}>
          <span>{status === "submitting" ? "Sending…" : "Submit inquiry"}</span>
          {status !== "submitting" && <ArrowUpRight size={15} className="arrow-shift" />}
        </button>
      </div>
    </form>
  );
}

function FormSection({ number, title, children }: { number: string; title: string; children: React.ReactNode }) {
  return (
    <fieldset>
      <legend className="mb-7 flex w-full items-center gap-4">
        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-navy text-sm font-bold tabular-nums text-gold-light">{number}</span>
        <span className="text-[1.5rem] font-bold tracking-[-0.025em] text-navy">{title}</span>
        <span aria-hidden="true" className="ml-2 h-px flex-1 bg-line" />
      </legend>
      <div className="grid gap-x-6 gap-y-6 md:grid-cols-2">{children}</div>
    </fieldset>
  );
}
