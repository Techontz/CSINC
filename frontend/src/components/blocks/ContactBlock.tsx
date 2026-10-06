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
    <section className="py-16 md:py-24">
      <div className="container-site grid gap-16 lg:grid-cols-12">
        <div className="lg:col-span-7">
          <Reveal>
            {data.heading && <h2 className="font-serif text-h2 text-navy">{data.heading}</h2>}
            {data.intro && <p className="mt-5 max-w-xl text-muted">{data.intro}</p>}
          </Reveal>
          <div className="relative mt-14">
            <Suspense fallback={<div className="h-96 animate-pulse bg-mist" aria-hidden="true" />}>
              <ContactForm options={options} />
            </Suspense>
          </div>
        </div>

        <aside className="lg:col-span-4 lg:col-start-9">
          <div className="space-y-px bg-line lg:sticky lg:top-[6.5rem]">
            <ContactCard icon={<MapPin />} title="Our office">
              <address className="not-italic leading-relaxed">
                {contact.address.line_1}
                {contact.address.line_2 && <>, {contact.address.line_2}</>}
                <br />
                {[contact.address.city, contact.address.region, contact.address.postal_code].filter(Boolean).join(", ")}
                <br />
                {contact.address.country}
              </address>
              <a href={`https://www.google.com/maps/search/?api=1&query=${mapQuery}`} target="_blank" rel="noopener noreferrer" className="group mt-4 inline-flex items-center gap-2 text-sm font-semibold text-navy">
                <span className="link-underline">Get directions</span>
                <ArrowUpRight size={14} className="arrow-shift text-gold" />
              </a>
            </ContactCard>
            <ContactCard icon={<Mail />} title="Email us">
              <p>General & support inquiries:</p>
              <a href={`mailto:${contact.email}`} className="link-underline mt-1 inline-block font-medium text-navy">{contact.email}</a>
            </ContactCard>
            {contact.phone && (
              <ContactCard icon={<Phone />} title="Call us">
                <a href={`tel:${contact.phone.replace(/[^0-9+]/g, "")}`} className="font-medium text-navy">{contact.phone}</a>
                {contact.hours && <p className="mt-1 text-sm">{contact.hours}</p>}
              </ContactCard>
            )}
            {contact.consultation_url && (
              <ContactCard icon={<Calendar />} title="Prefer to schedule?">
                <p>Book a consultation and we’ll get back to you promptly.</p>
                <SmartLink href={contact.consultation_url} className="group mt-5 inline-flex items-center gap-3 bg-navy px-5 py-3 text-[0.72rem] font-semibold uppercase tracking-[0.14em] text-white transition-colors hover:bg-navy-deep">
                  Schedule a consultation
                  <ArrowUpRight size={14} className="arrow-shift" />
                </SmartLink>
              </ContactCard>
            )}
          </div>
        </aside>
      </div>
    </section>
  );
}

function ContactCard({ icon, title, children }: { icon: React.ReactNode; title: string; children: React.ReactNode }) {
  return (
    <div className="bg-paper p-8">
      <div className="flex items-center gap-3 text-gold">
        {icon}
        <h3 className="eyebrow text-navy">{title}</h3>
      </div>
      <div className="mt-5 text-muted">{children}</div>
    </div>
  );
}
