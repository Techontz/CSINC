import type { BlockMap, Service } from "@/lib/types";
import { Accent } from "@/components/ui/Accent";
import { ArrowLink } from "@/components/ui/Button";
import { ArrowUpRight, Check } from "@/components/ui/Icons";
import { SmartLink } from "@/components/ui/SmartLink";
import { CmsImage } from "@/components/ui/CmsImage";
import { RichText } from "@/components/ui/RichText";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";
import { cn } from "@/lib/cn";

function Highlights({ items, tone = "dark" }: { items: string[]; tone?: "dark" | "light" }) {
  if (items.length === 0) {
    return null;
  }

  return (
    <ul className="mt-8 flex flex-wrap gap-2">
      {items.map((item) => (
        <li
          key={item}
          className={cn(
            "rounded-full px-3.5 py-1.5 text-[0.8125rem] font-semibold",
            tone === "dark" ? "bg-sky text-navy" : "bg-white/10 text-white/85",
          )}
        >
          {item}
        </li>
      ))}
    </ul>
  );
}

export function Services({ data }: { data: BlockMap["services"] }) {
  if (data.layout === "grid") {
    return (
      <section className="section-y relative isolate overflow-hidden bg-[linear-gradient(180deg,var(--color-paper)_0%,#ffffff_100%)]">
        <div aria-hidden="true" className="absolute -left-40 -top-40 -z-10 h-[34rem] w-[34rem] rounded-full border border-navy/[0.06]" />
        <div aria-hidden="true" className="absolute -right-32 top-1/3 -z-10 h-[28rem] w-[28rem] rounded-full bg-gold/[0.07] blur-3xl" />
        <div className="container-site max-w-[84rem]">
          <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
          <ul className="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-3 lg:gap-7">
            {data.services.map((service, index) => (
              <Reveal as="li" key={service.id} delay={(index % 3) * 90} className="h-full">
                <PremiumCard service={service} index={index} />
              </Reveal>
            ))}
          </ul>
        </div>
      </section>
    );
  }

  return (
    <section className="section-y bg-white">
      <div className="container-site">
        <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
        <div className="mt-16 space-y-20 md:mt-24 md:space-y-32">
          {data.services.map((service, index) => (
            <ServiceRow key={service.id} service={service} index={index} />
          ))}
        </div>
      </div>
    </section>
  );
}

function ServiceRow({ service, index }: { service: Service; index: number }) {
  const reversed = index % 2 === 1;

  return (
    <article id={service.slug} className="grid scroll-mt-28 items-center gap-10 lg:grid-cols-12 lg:gap-16">
      <div className={cn("relative isolate lg:col-span-6", reversed && "lg:order-2")}>
        <Reveal variant="mask" className="relative aspect-[5/4] overflow-hidden rounded-[1.75rem] bg-mist">
          <div className="reveal-scale absolute inset-0">
            <CmsImage image={service.image} sizes="(min-width: 1024px) 45vw, 100vw" />
          </div>
        </Reveal>
      </div>
      <Reveal className={cn("lg:col-span-5", reversed ? "lg:order-1 lg:col-start-1" : "lg:col-start-8")} delay={100}>
        <span className="text-sm font-bold tracking-[0.1em] text-gold-ink">{String(index + 1).padStart(2, "0")}</span>
        <h3 className="mt-4 font-serif text-h2 text-navy">
          <Accent text={service.title} />
        </h3>
        {service.summary && <p className="mt-6 text-lead text-muted">{service.summary}</p>}
        <RichText html={service.body} className="mt-6" />
        <Highlights items={service.highlights} />
        {service.link && (
          <div className="mt-10">
            <ArrowLink href={service.link.url}>{service.link.label}</ArrowLink>
          </div>
        )}
      </Reveal>
    </article>
  );
}

/** Elevated white card: framed photo with a glass index chip, title, checklist and a footer action. */
function PremiumCard({ service, index }: { service: Service; index: number }) {
  const link = service.link ?? { label: "Book a consultation", url: "/contact-us" };

  return (
    <article
      id={service.slug}
      className="group relative flex h-full scroll-mt-28 flex-col overflow-hidden rounded-[2rem] bg-white p-2.5 shadow-[0_1px_0_rgba(0,28,53,0.04),0_2px_6px_-2px_rgba(0,28,53,0.08),0_30px_60px_-40px_rgba(0,41,76,0.35)] ring-1 ring-navy/[0.06] transition-[transform,box-shadow] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-1.5 hover:shadow-[0_1px_0_rgba(0,28,53,0.04),0_40px_80px_-36px_rgba(0,41,76,0.5)]"
    >
      <div className="relative aspect-[4/3] overflow-hidden rounded-[1.5rem] bg-navy">
        <CmsImage image={service.image} sizes="(min-width: 1024px) 30vw, (min-width: 768px) 45vw, 100vw" className="img-zoom" />
        <div aria-hidden="true" className="absolute inset-0 bg-gradient-to-t from-navy-deep/55 via-navy-deep/0 to-navy-deep/10" />
        <span className="absolute left-4 top-4 rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold tabular-nums tracking-[0.08em] text-navy shadow-sm backdrop-blur">
          {String(index + 1).padStart(2, "0")}
        </span>
      </div>

      <div className="flex flex-1 flex-col px-5 pb-5 pt-7 md:px-6">
        <h3 className="text-[1.625rem] font-bold leading-[1.1] tracking-[-0.03em] text-navy">{service.title}</h3>
        <span aria-hidden="true" className="mt-4 block h-1 w-10 rounded-full bg-gold transition-[width] duration-500 group-hover:w-16" />
        {service.summary && <p className="mt-4 text-[0.98rem] leading-relaxed text-muted">{service.summary}</p>}

        {service.highlights.length > 0 && (
          <ul className="mt-6 space-y-2.5">
            {service.highlights.map((item) => (
              <li key={item} className="flex items-start gap-3 text-[0.9375rem] font-medium text-navy">
                <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-gold/15 text-gold-ink">
                  <Check size={12} strokeWidth={3} />
                </span>
                {item}
              </li>
            ))}
          </ul>
        )}

        <div className="mt-auto pt-8">
        <SmartLink
          href={link.url}
          className="flex items-center justify-between gap-4 rounded-2xl bg-sky px-5 py-4 text-[0.9375rem] font-semibold text-navy transition-colors duration-300 group-hover:bg-navy group-hover:text-white"
        >
          {link.label}
          <span className="flex h-9 w-9 items-center justify-center rounded-full bg-white text-navy shadow-sm transition-colors duration-300 group-hover:bg-gold group-hover:text-white">
            <ArrowUpRight size={16} strokeWidth={2.25} className="arrow-shift" />
          </span>
        </SmartLink>
        </div>
      </div>
    </article>
  );
}
