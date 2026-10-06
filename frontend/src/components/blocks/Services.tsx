import type { BlockMap, Service } from "@/lib/types";
import { Accent } from "@/components/ui/Accent";
import { ArrowLink } from "@/components/ui/Button";
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
            "border px-3.5 py-2 text-[0.7rem] font-semibold uppercase tracking-[0.14em]",
            tone === "dark" ? "border-navy/15 text-navy" : "border-white/20 text-white/85",
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
      <section className="section-y bg-paper">
        <div className="container-site">
          <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
          <ul className="mt-16 grid gap-px bg-line md:grid-cols-2 lg:grid-cols-3">
            {data.services.map((service, index) => (
              <Reveal as="li" key={service.id} delay={(index % 3) * 90} className="group flex flex-col bg-paper">
                <article id={service.slug} className="flex h-full flex-col p-0 md:p-2">
                  <div className="relative aspect-[16/10] overflow-hidden bg-mist">
                    <CmsImage image={service.image} sizes="(min-width: 1024px) 30vw, (min-width: 768px) 45vw, 100vw" className="img-zoom" />
                  </div>
                  <div className="flex flex-1 flex-col px-1 pb-10 pt-8 md:px-6">
                    <span className="text-sm font-bold tracking-[0.1em] text-gold-ink">{String(index + 1).padStart(2, "0")}</span>
                    <h3 className="mt-3 text-h3 font-normal text-navy">{service.title}</h3>
                    {service.summary && <p className="mt-4 leading-relaxed text-muted">{service.summary}</p>}
                    <Highlights items={service.highlights} />
                    {service.link && (
                      <div className="mt-auto pt-8">
                        <ArrowLink href={service.link.url}>{service.link.label}</ArrowLink>
                      </div>
                    )}
                  </div>
                </article>
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
        <Reveal variant="mask" className="relative aspect-[5/4] overflow-hidden bg-mist">
          <div className="reveal-scale absolute inset-0">
            <CmsImage image={service.image} sizes="(min-width: 1024px) 45vw, 100vw" />
          </div>
        </Reveal>
        <div aria-hidden="true" className={cn("absolute -bottom-3 h-full w-full bg-navy", reversed ? "-right-3" : "-left-3")} style={{ zIndex: -1 }} />
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
