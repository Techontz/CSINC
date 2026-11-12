import type { BlockMap } from "@/lib/types";
import { cn } from "@/lib/cn";
import { Accent } from "@/components/ui/Accent";
import { ButtonLink } from "@/components/ui/Button";
import { CmsImage } from "@/components/ui/CmsImage";
import { ArrowUpRight } from "@/components/ui/Icons";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";
import { SmartLink } from "@/components/ui/SmartLink";

/** Each figure gets its own tone, drawn from the navy and gold palette. */
const TONES = ["text-gold-light", "text-[#9cc3ea]", "text-white", "text-gold"];

/** Deep navy “impact” band: big coloured figures beside a dotted photographic panel. */
export function Results({ data }: { data: BlockMap["results"] }) {
  const items = data.items;
  const feature = items.find((item) => item.image)?.image ?? null;

  return (
    <section className="relative isolate overflow-hidden bg-navy-deep py-20 text-white md:py-28">
      <div className="container-site grid max-w-[84rem] items-center gap-14 lg:grid-cols-12 lg:gap-12">
        <div className="lg:col-span-6">
          <Reveal>
            {data.eyebrow && <Eyebrow tone="light" className="mb-5">{data.eyebrow}</Eyebrow>}
            <h2 className="font-serif text-h2 text-balance text-white [&_em]:text-gold-light">
              <Accent text={data.heading} />
            </h2>
          </Reveal>

          <ul className={cn("mt-12 grid gap-x-8 gap-y-10", items.length === 3 ? "sm:grid-cols-3" : "sm:grid-cols-2")}>
            {items.map((item, index) => {
              const body = (
                <>
                  <div>
                    <span className={cn("block text-[clamp(2.75rem,2rem+2.6vw,4rem)] font-bold leading-none tracking-[-0.04em]", TONES[index % TONES.length])}>{item.value}</span>
                    {item.eyebrow && <span className="mt-4 block text-base font-semibold text-white">{item.eyebrow}</span>}
                    <span className="mt-1.5 block text-[0.9375rem] leading-relaxed text-white/65">{item.label}</span>
                    {item.url && (
                      <span className="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-white/80 transition-colors group-hover:text-gold-light">
                        Explore <ArrowUpRight size={14} strokeWidth={2.25} className="arrow-shift" />
                      </span>
                    )}
                  </div>
                </>
              );
              return (
                <Reveal as="li" key={index} delay={index * 90}>
                  {item.url ? (
                    <SmartLink href={item.url} className="group block">{body}</SmartLink>
                  ) : (
                    <div>{body}</div>
                  )}
                </Reveal>
              );
            })}
          </ul>

          {data.link_url && data.link_label && (
            <Reveal delay={200} className="mt-12">
              <ButtonLink href={data.link_url} variant="outline-light">{data.link_label}</ButtonLink>
            </Reveal>
          )}
        </div>

        <Reveal delay={120} className="relative lg:col-span-6">
          <div className="relative aspect-[5/4] overflow-hidden rounded-[2rem] bg-navy lg:aspect-[4/5] lg:translate-x-[6%]">
            <CmsImage image={feature} sizes="(min-width: 1024px) 45vw, 100vw" className="opacity-70" quality={70} />
            <div aria-hidden="true" className="absolute inset-0 bg-gradient-to-tr from-navy-deep via-navy-deep/40 to-transparent" />
            {/* Dot-matrix texture */}
            <div
              aria-hidden="true"
              className="absolute inset-0 opacity-50 [mask-image:linear-gradient(to_left,black,transparent_75%)]"
              style={{ backgroundImage: "radial-gradient(rgba(231,207,159,0.55) 1.2px, transparent 1.4px)", backgroundSize: "14px 14px" }}
            />
          </div>
        </Reveal>
      </div>
    </section>
  );
}
