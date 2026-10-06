import Link from "next/link";
import type { SiteData } from "@/lib/types";
import { ArrowUpRight, SocialIcon } from "@/components/ui/Icons";
import { SmartLink } from "@/components/ui/SmartLink";
import { Logo } from "./Logo";

/** Light, compact footer in the reference style. */
export function SiteFooter({ site }: { site: SiteData }) {
  const { contact } = site;
  const address = [contact.address.line_1, contact.address.city, contact.address.region, contact.address.postal_code].filter(Boolean).join(", ");

  return (
    <footer className="bg-[#eef3f9] text-navy">
      <div className="container-site grid gap-12 py-14 md:grid-cols-3 md:items-start md:gap-8 md:py-16">
        <div>
          <Logo name={site.name} variant="full" />
          <p className="mt-6 text-sm text-muted">© {new Date().getFullYear()} Csinc 91. All Rights Reserved.</p>
          <ul className="mt-3 space-y-1.5 text-sm">
            {site.navigation.legal.map((link) => (
              <li key={link.url}>
                <SmartLink href={link.url} className="text-navy/80 transition-colors hover:text-gold">{link.label}</SmartLink>
              </li>
            ))}
          </ul>
        </div>

        <div className="md:text-center">
          <p className="font-serif text-2xl">Get in touch</p>
          <Link
            href="/contact-us"
            className="group mt-5 inline-flex items-center gap-3 border border-gold px-5 py-3 text-sm font-bold uppercase tracking-[0.1em] text-navy transition-colors hover:bg-navy hover:text-white"
          >
            Contact us <ArrowUpRight size={16} strokeWidth={2.25} className="arrow-shift" />
          </Link>
          <p className="mt-6 text-sm leading-relaxed text-muted">
            {address}
            {contact.address.country ? `, ${contact.address.country}` : ""}
            <br />
            <a href={`mailto:${contact.email}`} className="text-navy underline decoration-gold/60 underline-offset-4 hover:decoration-navy">{contact.email}</a>
            {contact.phone && (
              <>
                {" · "}
                <a href={`tel:${contact.phone.replace(/[^0-9+]/g, "")}`} className="text-navy">{contact.phone}</a>
              </>
            )}
          </p>
        </div>

        <div className="md:text-right">
          <ul className="flex flex-wrap gap-x-6 gap-y-2.5 text-sm font-medium md:justify-end">
            {site.navigation.footer.map((link) => (
              <li key={link.url}>
                <SmartLink href={link.url} newTab={link.new_tab} className="transition-colors hover:text-gold">{link.label}</SmartLink>
              </li>
            ))}
          </ul>
          {site.social.length > 0 && (
            <ul className="mt-6 flex gap-3 md:justify-end">
              {site.social.map((link) => (
                <li key={link.url}>
                  <a href={link.url} target="_blank" rel="noopener noreferrer" aria-label={link.platform} className="flex h-9 w-9 items-center justify-center rounded-full bg-navy text-white transition-colors hover:bg-gold">
                    <SocialIcon platform={link.platform} size={15} />
                  </a>
                </li>
              ))}
            </ul>
          )}
          {contact.consultation_url && (
            <SmartLink href={contact.consultation_url} className="group mt-6 inline-flex items-center gap-2 text-sm font-bold uppercase tracking-[0.1em] text-navy">
              <span className="link-underline">Schedule a consultation</span>
              <ArrowUpRight size={15} strokeWidth={2.25} className="arrow-shift text-gold" />
            </SmartLink>
          )}
        </div>
      </div>
    </footer>
  );
}
