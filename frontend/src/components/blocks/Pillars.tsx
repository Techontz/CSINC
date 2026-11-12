import type { BlockMap } from "@/lib/types";
import { cn } from "@/lib/cn";
import { Accent } from "@/components/ui/Accent";
import { ButtonLink } from "@/components/ui/Button";
import { CmsImage } from "@/components/ui/CmsImage";
import { ArrowUpRight } from "@/components/ui/Icons";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";
import { SmartLink } from "@/components/ui/SmartLink";

type Item = BlockMap["pillars"]["items"][number];

/**
 * Column spans on the 5-column desktop grid: rows alternate wide|narrow and
 * narrow|wide. A last card that would sit alone on its row spans the full width.
 */
function spans(count: number): number[] {
  const pattern = [3, 2, 2, 3];
  const result: number[] = [];
  let filled = 0;
  for (let index = 0; index < count; index++) {
    const isLastAlone = index === count - 1 && filled === 0;
    const span = isLastAlone ? 5 : pattern[index % 4];
    result.push(span);
    filled = (filled + span) % 5;
  }
  return result;
}

/** Bento grid of tinted, rounded cards with imagery and a floating chip. */
export function Pillars({ data }: { data: BlockMap["pillars"] }) {
  const layout = spans(data.items.length);

  return (
    <section className="section-y bg-white">
      <div className="container-site max-w-[84rem]">
        <Reveal className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <div className="max-w-2xl">
            {data.eyebrow && <Eyebrow className="mb-5">{data.eyebrow}</Eyebrow>}
            <h2 className="font-serif text-h2 text-balance text-navy">
              <Accent text={data.heading} />
            </h2>
          </div>
          {data.link_url && data.link_label && (
            <ButtonLink href={data.link_url} variant="outline" className="self-start md:self-auto">
              {data.link_label}
            </ButtonLink>
          )}
        </Reveal>

        <ul className="mt-12 grid gap-5 md:grid-cols-2 lg:grid-cols-5 lg:gap-6">
          {data.items.map((item, index) => {
            const span = layout[index];
            const wide = span >= 3;
            return (
              <Reveal
                as="li"
                key={item.title}
                delay={index * 90}
                className={cn(span === 5 && "md:col-span-2 lg:col-span-5", span === 3 && "lg:col-span-3", span === 2 && "lg:col-span-2")}
              >
                {item.link_url ? (
                  <SmartLink href={item.link_url} className="group block h-full rounded-[1.75rem] focus-visible:outline-offset-4">
                    <BentoCard item={item} wide={wide} full={span === 5} />
                  </SmartLink>
                ) : (
                  <div className="group h-full">
                    <BentoCard item={item} wide={wide} full={span === 5} />
                  </div>
                )}
              </Reveal>
            );
          })}
        </ul>
      </div>
    </section>
  );
}

function BentoCard({ item, wide, full }: { item: Item; wide: boolean; full: boolean }) {
  return (
    <article
      className={cn(
        "relative flex h-full min-h-[26rem] flex-col overflow-hidden rounded-[1.75rem] bg-sky transition-[box-shadow,transform] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:-translate-y-1 group-hover:shadow-[0_30px_60px_-30px_rgba(0,41,76,0.45)]",
        wide && "md:flex-row",
      )}
    >
      <div className={cn("relative z-10 flex flex-col p-7 md:p-9", wide ? "md:w-1/2 md:pr-4" : "", full && "md:w-[42%] md:justify-center md:p-12")}>
        <h3 className="text-[clamp(1.625rem,1.2rem+1.4vw,2.375rem)] font-bold leading-[1.05] tracking-[-0.03em] text-navy">{item.title}</h3>
        {item.body && <p className="mt-4 max-w-md text-[0.9875rem] leading-relaxed text-navy/70">{item.body}</p>}
        {item.link_url && (
          <span className="mt-6 inline-flex items-center gap-2 text-[0.9375rem] font-semibold text-navy">
            <span className="link-underline pb-0.5">{item.link_label ?? "Learn more"}</span>
            <span className="flex h-7 w-7 items-center justify-center rounded-full bg-navy text-white transition-colors group-hover:bg-gold">
              <ArrowUpRight size={14} strokeWidth={2.5} className="arrow-shift" />
            </span>
          </span>
        )}
      </div>

      <div className={cn("relative mt-auto flex flex-1 items-end px-7 md:px-9", wide ? "md:mt-0 md:w-1/2 md:pl-0 md:pt-9" : "", full && "md:w-[58%]")}>
        <div
          className={cn(
            "relative w-full overflow-hidden rounded-t-2xl bg-sky-deep",
            wide ? "aspect-[4/3] md:aspect-auto md:h-full md:min-h-[20rem]" : "aspect-[16/11]",
          )}
        >
          <CmsImage image={item.image} sizes={full ? "(min-width: 1024px) 50vw, 100vw" : "(min-width: 1024px) 30vw, (min-width: 768px) 45vw, 100vw"} className="img-zoom" />
        </div>
        {item.link_label && (
          <span className="float-chip absolute bottom-6 left-10 max-w-[70%] md:left-12">
            <span className="block text-[0.6875rem] font-medium text-muted">{item.title}</span>
            <span className="mt-0.5 flex items-center gap-2 text-[0.9375rem] font-bold leading-tight">
              {item.link_label}
              <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-gold/15 text-gold-ink">
                <ArrowUpRight size={12} strokeWidth={2.75} />
              </span>
            </span>
          </span>
        )}
      </div>
    </article>
  );
}
