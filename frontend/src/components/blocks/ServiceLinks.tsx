import type { BlockMap, Service } from "@/lib/types";
import { Accent } from "@/components/ui/Accent";
import { FramedImage } from "@/components/ui/FramedImage";
import { ArrowUpRight } from "@/components/ui/Icons";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";
import { SmartLink } from "@/components/ui/SmartLink";

/** Healthcare duplicates of business offerings are listed once, under Services. */
const DUPLICATES = new Set(["healthcare-business-services", "healthcare-digital-products", "products-guided-resources"]);

function serviceHref(service: Service): string {
  return `${service.group === "healthcare" ? "/healthcare" : "/services"}#${service.slug}`;
}

/** Navy 50/50 band: framed image beside a heading and a stacked list of outlined service links. */
export function ServiceLinks({ data }: { data: BlockMap["service_links"] }) {
  const services = data.services.filter((service) => !DUPLICATES.has(service.slug));

  return (
    <section className="overflow-hidden bg-navy py-16 text-white md:py-24">
      <div className="container-site grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
        <FramedImage image={data.image} className="mx-auto w-full max-w-[36rem] lg:max-w-none" />
        <Reveal delay={120}>
          {data.eyebrow && <Eyebrow tone="light" className="mb-4">{data.eyebrow}</Eyebrow>}
          <h2 className="font-serif text-h2 text-white [&_em]:block [&_em]:text-gold-light">
            <Accent text={data.heading} />
          </h2>
          {data.body && <p className="mt-6 max-w-xl text-lead text-white/80">{data.body}</p>}
          <ul className="mt-9 flex flex-col items-start gap-3">
            {services.map((service) => (
              <li key={service.id}>
                <SmartLink
                  href={serviceHref(service)}
                  className="group inline-flex items-center gap-3 border border-gold-light/60 px-4 py-2.5 text-[0.9375rem] font-bold text-gold-light transition-colors hover:border-gold-light hover:bg-gold-light hover:text-navy-deep"
                >
                  {service.title}
                  <ArrowUpRight size={16} strokeWidth={2.25} className="arrow-shift" />
                </SmartLink>
              </li>
            ))}
          </ul>
        </Reveal>
      </div>
    </section>
  );
}
