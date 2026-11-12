import type { BlockMap } from "@/lib/types";
import { cn } from "@/lib/cn";
import { Accent } from "@/components/ui/Accent";
import { ButtonLink } from "@/components/ui/Button";
import { CmsImage } from "@/components/ui/CmsImage";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

/** Rounded photographic panel with the statement set on one side. */
export function CtaBand({ data }: { data: BlockMap["cta_band"] }) {
  const right = (data.align ?? "right") === "right";

  return (
    <section className="bg-white pb-[clamp(3.5rem,2.4rem+4.4vw,6.25rem)] pt-[clamp(1.5rem,1rem+2vw,3rem)]">
      <div className="container-site max-w-[84rem]">
        <div className="relative isolate overflow-hidden rounded-[2rem] bg-navy-deep text-white">
          <CmsImage image={data.background} sizes="(min-width: 1344px) 1300px, 100vw" className="-z-20" quality={70} />
          <div
            aria-hidden="true"
            className={cn(
              "absolute inset-0 -z-10 bg-navy-deep/65 md:bg-transparent",
              right ? "md:bg-gradient-to-l md:from-navy-deep/95 md:via-navy-deep/65 md:to-transparent" : "md:bg-gradient-to-r md:from-navy-deep/95 md:via-navy-deep/65 md:to-transparent",
            )}
          />
          <div className="flex min-h-[28rem] items-center px-7 py-16 md:min-h-[34rem] md:px-14">
            <div className={cn("max-w-xl", right && "md:ml-auto md:w-[46%] md:max-w-none")}>
              <Reveal>
                {data.eyebrow && <Eyebrow tone="light" className="mb-5">{data.eyebrow}</Eyebrow>}
                <h2 className="font-serif text-h2 text-balance [&_em]:text-gold-light">
                  <Accent text={data.heading} />
                </h2>
                {data.body && <p className="mt-5 max-w-lg text-lead text-white/80">{data.body}</p>}
              </Reveal>
              <Reveal delay={150} className="mt-9 flex flex-col gap-3 sm:flex-row">
                {data.primary_url && data.primary_label && <ButtonLink href={data.primary_url} variant="gold">{data.primary_label}</ButtonLink>}
                {data.secondary_url && data.secondary_label && <ButtonLink href={data.secondary_url} variant="outline-light">{data.secondary_label}</ButtonLink>}
              </Reveal>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
