import type { BlockMap } from "@/lib/types";
import { Accent } from "@/components/ui/Accent";
import { ArrowLink } from "@/components/ui/Button";
import { CmsImage } from "@/components/ui/CmsImage";
import { ArrowUpRight } from "@/components/ui/Icons";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";
import { SmartLink } from "@/components/ui/SmartLink";

/** Three-up teaser cards: image, accent rule on the left and bottom, sans title. */
export function Pillars({ data }: { data: BlockMap["pillars"] }) {
  return (
    <section className="section-y bg-white">
      <div className="container-site">
        <Reveal className="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
          <div>
            {data.eyebrow && <Eyebrow className="mb-3">{data.eyebrow}</Eyebrow>}
            <h2 className="font-serif text-h2s text-navy">
              <Accent text={data.heading} />
            </h2>
          </div>
          {data.link_url && data.link_label && <ArrowLink href={data.link_url}>{data.link_label}</ArrowLink>}
        </Reveal>

        <ul className="mt-10 grid gap-8 md:grid-cols-2 lg:grid-cols-3 lg:gap-7">
          {data.items.map((item, index) => {
            const card = (
              <article className="flex h-full flex-col border-b-4 border-l-4 border-gold bg-white transition-shadow duration-500 group-hover:shadow-[0_24px_48px_-28px_rgba(0,28,53,0.45)]">
                <div className="relative aspect-[404/328] overflow-hidden bg-mist">
                  <CmsImage image={item.image} sizes="(min-width: 1024px) 30vw, (min-width: 768px) 45vw, 100vw" className="img-zoom" />
                </div>
                <div className="flex flex-1 flex-col px-7 pb-6 pt-8 md:px-9 md:pt-9">
                  <h3 className="text-h3 font-normal text-navy transition-colors group-hover:text-gold-ink">{item.title}</h3>
                  {item.body && <p className="mt-4 leading-relaxed text-muted">{item.body}</p>}
                  {item.link_url && (
                    <span className="mt-auto inline-flex items-center gap-2 pt-7 text-sm font-bold uppercase tracking-[0.1em] text-navy">
                      {item.link_label ?? "Learn more"}
                      <ArrowUpRight size={16} strokeWidth={2.25} className="arrow-shift text-gold" />
                    </span>
                  )}
                </div>
              </article>
            );

            return (
              <Reveal as="li" key={item.title} delay={index * 110}>
                {item.link_url ? (
                  <SmartLink href={item.link_url} className="group block h-full">{card}</SmartLink>
                ) : (
                  <div className="group h-full">{card}</div>
                )}
              </Reveal>
            );
          })}
        </ul>
      </div>
    </section>
  );
}
