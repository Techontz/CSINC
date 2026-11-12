import type { BlockMap, Service } from "@/lib/types";
import { Accent } from "@/components/ui/Accent";
import { CmsImage } from "@/components/ui/CmsImage";
import { ArrowUpRight } from "@/components/ui/Icons";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";
import { SmartLink } from "@/components/ui/SmartLink";

/** Healthcare duplicates of business offerings are listed once, under Services. */
const DUPLICATES = new Set(["healthcare-business-services", "healthcare-digital-products", "products-guided-resources"]);

function serviceHref(service: Service): string {
  return `${service.group === "healthcare" ? "/healthcare" : "/services"}#${service.slug}`;
}

/** Brand-colour band: rounded image beside a numbered two-column list of services. */
export function ServiceLinks({ data }: { data: BlockMap["service_links"] }) {
  const services = data.services.filter((service) => !DUPLICATES.has(service.slug));

  return (
    <section className="relative isolate overflow-hidden bg-navy py-20 text-white md:py-28">
      {/* Soft concentric arcs */}
      <div aria-hidden="true" className="pointer-events-none absolute -right-[20rem] -top-[24rem] -z-10 h-[56rem] w-[56rem] rounded-full border border-white/[0.06]" />
      <div aria-hidden="true" className="pointer-events-none absolute -right-[12rem] -top-[16rem] -z-10 h-[40rem] w-[40rem] rounded-full border border-white/[0.06]" />
      <div aria-hidden="true" className="pointer-events-none absolute -bottom-[30rem] -left-[20rem] -z-10 h-[50rem] w-[50rem] rounded-full bg-navy-soft/40 blur-3xl" />

      <div className="container-site max-w-[84rem]">
        <Reveal className="max-w-3xl">
          {data.eyebrow && <Eyebrow tone="light" className="mb-5">{data.eyebrow}</Eyebrow>}
          <h2 className="font-serif text-h2 text-balance text-white [&_em]:text-gold-light">
            <Accent text={data.heading} />
          </h2>
          {data.body && <p className="mt-5 max-w-2xl text-lead text-white/75">{data.body}</p>}
        </Reveal>

        <div className="mt-14 grid items-start gap-10 lg:grid-cols-12 lg:gap-14">
          <Reveal className="relative lg:sticky lg:top-28 lg:col-span-5">
            <div className="relative aspect-[4/5] overflow-hidden rounded-[1.75rem] bg-navy-soft">
              <CmsImage image={data.image} sizes="(min-width: 1024px) 38vw, 100vw" className="img-zoom" />
              <div aria-hidden="true" className="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-navy-deep/60 to-transparent" />
            </div>
            <div className="float-chip absolute -bottom-5 left-6 flex items-center gap-3 md:left-8">
              <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-gold text-lg font-bold text-white">{services.length}</span>
              <span>
                <span className="block text-[0.6875rem] font-medium text-muted">Services</span>
                <span className="block text-[0.9375rem] font-bold leading-tight">End-to-end support</span>
              </span>
            </div>
          </Reveal>

          <ol className="grid gap-3 sm:grid-cols-2 lg:col-span-7">
            {services.map((service, index) => (
              <Reveal as="li" key={service.id} delay={(index % 2) * 80}>
                <SmartLink
                  href={serviceHref(service)}
                  className="group flex h-full gap-4 rounded-2xl border border-white/10 bg-white/[0.04] p-5 transition-colors duration-300 hover:border-white/25 hover:bg-white/[0.09] md:p-6"
                >
                  <span className="pt-0.5 text-sm font-semibold tabular-nums text-gold-light">{String(index + 1).padStart(2, "0")}</span>
                  <span className="flex flex-1 flex-col">
                    <span className="flex items-start justify-between gap-3">
                      <span className="text-lg font-bold leading-snug tracking-[-0.015em]">{service.title}</span>
                      <ArrowUpRight size={16} strokeWidth={2.25} className="arrow-shift mt-1 shrink-0 text-white/50 group-hover:text-gold-light" />
                    </span>
                    {service.summary && <span className="mt-2 line-clamp-2 text-sm leading-relaxed text-white/65">{service.summary}</span>}
                  </span>
                </SmartLink>
              </Reveal>
            ))}
          </ol>
        </div>
      </div>
    </section>
  );
}
