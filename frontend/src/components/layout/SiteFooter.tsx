import type { SiteData } from "@/lib/types";
import { ButtonLink } from "@/components/ui/Button";
import { ArrowUpRight, SocialIcon } from "@/components/ui/Icons";
import { SmartLink } from "@/components/ui/SmartLink";
import { Logo } from "./Logo";

/** Deep navy footer: contact row, link columns, consultation card and legal line. */
export function SiteFooter({ site }: { site: SiteData }) {
  const { contact } = site;
  const address = [contact.address.line_1, contact.address.city, contact.address.region, contact.address.postal_code, contact.address.country].filter(Boolean).join(", ");
  const name = site.legal_name || site.name;
  const linkClass = "text-[0.9375rem] text-white/70 transition-colors hover:text-white";

  return (
    <footer className="bg-navy-deep text-white">
      <div className="container-site max-w-[84rem]">
        {/* Contact row */}
        <div className="flex flex-col gap-8 border-b border-white/10 py-14 md:flex-row md:items-center md:justify-between md:py-16">
          <div>
            <p className="text-[clamp(1.75rem,1.3rem+1.6vw,2.5rem)] font-bold leading-tight tracking-[-0.03em]">Get in touch</p>
            <p className="mt-2 max-w-md text-[0.9375rem] text-white/65">Talk to our team about your business, credit or care-facility goals.</p>
          </div>
          <div className="flex flex-col gap-3 sm:flex-row">
            <ButtonLink href="/contact-us" variant="gold">Contact us</ButtonLink>
            {contact.consultation_url && <ButtonLink href={contact.consultation_url} variant="outline-light">Schedule a consultation</ButtonLink>}
          </div>
        </div>

        {/* Main */}
        <div className="grid gap-12 py-14 md:grid-cols-12 md:gap-8">
          <div className="md:col-span-5">
            <Logo name={site.name} variant="full" inverse />
            {site.tagline && <p className="mt-5 max-w-sm text-[0.9375rem] leading-relaxed text-white/65">{site.tagline}</p>}
            {site.social.length > 0 && (
              <ul className="mt-7 flex gap-2.5">
                {site.social.map((link) => (
                  <li key={link.url}>
                    <a
                      href={link.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      aria-label={link.platform}
                      className="flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-gold"
                    >
                      <SocialIcon platform={link.platform} size={16} />
                    </a>
                  </li>
                ))}
              </ul>
            )}
          </div>

          <nav aria-label="Footer" className="md:col-span-2">
            <p className="text-sm font-semibold text-white/45">Explore</p>
            <ul className="mt-5 space-y-3.5">
              {site.navigation.footer.map((link) => (
                <li key={link.url}>
                  <SmartLink href={link.url} newTab={link.new_tab} className={linkClass}>{link.label}</SmartLink>
                </li>
              ))}
            </ul>
          </nav>

          {site.navigation.legal.length > 0 && (
            <nav aria-label="Legal" className="md:col-span-2">
              <p className="text-sm font-semibold text-white/45">Legal</p>
              <ul className="mt-5 space-y-3.5">
                {site.navigation.legal.map((link) => (
                  <li key={link.url}>
                    <SmartLink href={link.url} className={linkClass}>{link.label}</SmartLink>
                  </li>
                ))}
              </ul>
            </nav>
          )}

          <div className="md:col-span-3">
            <p className="text-sm font-semibold text-white/45">Contact</p>
            <ul className="mt-5 space-y-3.5 text-[0.9375rem]">
              <li>
                <a href={`mailto:${contact.email}`} className="group inline-flex items-center gap-1.5 text-white/70 transition-colors hover:text-white">
                  {contact.email}
                  <ArrowUpRight size={13} strokeWidth={2.25} className="arrow-shift text-gold-light" />
                </a>
              </li>
              {contact.phone && (
                <li>
                  <a href={`tel:${contact.phone.replace(/[^0-9+]/g, "")}`} className={linkClass}>{contact.phone}</a>
                </li>
              )}
              {address && <li className="leading-relaxed text-white/70">{address}</li>}
            </ul>
          </div>
        </div>

        <div className="flex flex-col gap-2 border-t border-white/10 py-7 text-sm text-white/50 md:flex-row md:items-center md:justify-between">
          <p>© {new Date().getFullYear()} {name}. All Rights Reserved.</p>
          <p>Secure checkout · Instant PDF downloads</p>
        </div>
      </div>
    </footer>
  );
}
