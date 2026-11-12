import { Suspense } from "react";
import type { BlockMap, ConsultationOptions, SiteData } from "@/lib/types";
import { ContactForm } from "@/components/forms/ContactForm";
import { ArrowUpRight, Calendar, Mail, MapPin, Phone } from "@/components/ui/Icons";
import { Reveal } from "@/components/ui/Reveal";
import { SmartLink } from "@/components/ui/SmartLink";

export function ContactBlock({ data, site, options }: { data: BlockMap["contact"]; site: SiteData; options: ConsultationOptions }) {
  const { contact } = site;
  const mapQuery = encodeURIComponent([contact.address.line_1, contact.address.city, contact.address.region, contact.address.postal_code].filter(Boolean).join(", "));

  return (
    <section className="relative isolate overflow-x-clip bg-[linear-gradient(180deg,#ffffff_0%,var(--color-paper)_30%,var(--color-paper)_100%)] pb-20 pt-4 md:pb-28 md:pt-6">
      <div aria-hidden="true" className="absolute -left-48 top-40 -z-10 h-[36rem] w-[36rem] rounded-full border border-navy/[0.06]" />
      <div aria-hidden="true" className="absolute -right-40 bottom-0 -z-10 h-[30rem] w-[30rem] rounded-full bg-gold/[0.07] blur-3xl" />
      <div className="container-site grid gap-12 lg:grid-cols-12 lg:gap-12">
        <div className="lg:col-span-8">
          <Reveal>
            {data.heading && <h2 className="font-serif text-h2s text-navy">{data.heading}</h2>}
            {data.intro && <p className="mt-5 max-w-xl text-lead text-muted">{data.intro}</p>}
          </Reveal>
          <div className="relative mt-10">
            <Suspense fallback={<div className="h-96 animate-pulse rounded-[2rem] bg-mist" aria-hidden="true" />}>
              <ContactForm options={options} />
            </Suspense>
          </div>
        </div>

        <aside className="lg:col-span-4 lg:pt-[7.25rem]">
          <div className="relative isolate overflow-hidden rounded-[2rem] bg-navy p-7 text-white shadow-[0_40px_80px_-40px_rgba(0,41,76,0.7)] md:p-8 lg:sticky lg:top-[7.5rem]">
            <div aria-hidden="true" className="absolute -right-24 -top-24 -z-10 h-64 w-64 rounded-full border border-white/10" />
            <div aria-hidden="true" className="absolute -right-10 -top-10 -z-10 h-36 w-36 rounded-full border border-gold-light/25" />
            <div aria-hidden="true" className="absolute -bottom-24 -left-16 -z-10 h-64 w-64 rounded-full bg-navy-soft blur-3xl" />

            <p className="text-[1.5rem] font-bold leading-tight tracking-[-0.025em]">Talk to our team</p>
            <p className="mt-2 text-[0.9375rem] leading-relaxed text-white/65">Visit, email or book a time. We reply within one business day.</p>

            <div className="mt-7 divide-y divide-white/10">
              <ContactItem icon={<MapPin size={20} />} title="Our office">
                <address className="not-italic leading-relaxed">
                  {contact.address.line_1}
                  {contact.address.line_2 && <>, {contact.address.line_2}</>}
                  <br />
                  {[contact.address.city, contact.address.region, contact.address.postal_code].filter(Boolean).join(", ")}
                  {contact.address.country && <>, {contact.address.country}</>}
                </address>
                <a href={`https://www.google.com/maps/search/?api=1&query=${mapQuery}`} target="_blank" rel="noopener noreferrer" className="group mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-gold-light">
                  <span className="link-underline">Get directions</span>
                  <ArrowUpRight size={14} strokeWidth={2.25} className="arrow-shift" />
                </a>
              </ContactItem>
              <ContactItem icon={<Mail size={20} />} title="Email us">
                <p className="text-sm text-white/55">General & support inquiries</p>
                <a href={`mailto:${contact.email}`} className="mt-1 inline-block break-all font-semibold text-white transition-colors hover:text-gold-light">{contact.email}</a>
              </ContactItem>
              {contact.phone && (
                <ContactItem icon={<Phone size={20} />} title="Call us">
                  <a href={`tel:${contact.phone.replace(/[^0-9+]/g, "")}`} className="font-semibold text-white transition-colors hover:text-gold-light">{contact.phone}</a>
                  {contact.hours && <p className="mt-1 text-sm text-white/55">{contact.hours}</p>}
                </ContactItem>
              )}
            </div>

            {contact.consultation_url && (
              <div className="mt-6 rounded-2xl bg-white/[0.06] p-5 ring-1 ring-white/10">
                <div className="flex items-center gap-3">
                  <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold text-white">
                    <Calendar size={19} />
                  </span>
                  <div>
                    <p className="font-semibold">Prefer to schedule?</p>
                    <p className="text-sm text-white/60">Pick a time that suits you.</p>
                  </div>
                </div>
                <SmartLink
                  href={contact.consultation_url}
                  className="group mt-5 flex items-center justify-between gap-3 rounded-xl bg-gold px-5 py-3.5 text-[0.9375rem] font-semibold text-white shadow-[0_12px_28px_-12px_rgba(185,133,44,0.9)] transition-colors hover:bg-white hover:text-navy"
                >
                  Schedule a consultation
                  <ArrowUpRight size={16} strokeWidth={2.25} className="arrow-shift" />
                </SmartLink>
              </div>
            )}
          </div>
        </aside>
      </div>
    </section>
  );
}

function ContactItem({ icon, title, children }: { icon: React.ReactNode; title: string; children: React.ReactNode }) {
  return (
    <div className="flex gap-4 py-5 first:pt-0 last:pb-0">
      <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-gold-light ring-1 ring-white/10">{icon}</span>
      <div className="min-w-0 text-[0.9375rem] text-white/80">
        <h3 className="mb-1.5 text-base font-bold text-white">{title}</h3>
        {children}
      </div>
    </div>
  );
}
