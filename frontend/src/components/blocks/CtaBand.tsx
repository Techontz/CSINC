import type { BlockMap } from "@/lib/types";
import { cn } from "@/lib/cn";
import { Accent } from "@/components/ui/Accent";
import { ButtonLink } from "@/components/ui/Button";
import { CmsImage } from "@/components/ui/CmsImage";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

/** Full-bleed photographic band with the statement set on one side. */
export function CtaBand({ data }: { data: BlockMap["cta_band"] }) {
  const right = (data.align ?? "right") === "right";

  return (
    <section className="relative isolate overflow-hidden bg-navy-deep text-white">
      <CmsImage image={data.background} sizes="100vw" className="-z-20" quality={70} />
      <div
        aria-hidden="true"
        className={cn(
          "absolute inset-0 -z-10 bg-navy-deep/60 md:bg-transparent",
          right ? "md:bg-gradient-to-l md:from-navy-deep/95 md:via-navy-deep/70 md:to-navy-deep/5" : "md:bg-gradient-to-r md:from-navy-deep/95 md:via-navy-deep/70 md:to-navy-deep/5",
        )}
      />
      <div className="container-site flex min-h-[26rem] items-center py-20 md:min-h-[30rem]">
        <div className={cn("max-w-xl", right && "md:ml-auto md:w-1/2 md:max-w-none md:pl-6")}>
          <Reveal>
            {data.eyebrow && <Eyebrow tone="light" className="mb-4">{data.eyebrow}</Eyebrow>}
            <h2 className="font-serif text-h2 [&_em]:text-gold-light">
              <Accent text={data.heading} />
            </h2>
            {data.body && <p className="mt-5 max-w-lg text-lead text-white/80">{data.body}</p>}
          </Reveal>
          <Reveal delay={150} className="mt-9 flex flex-col gap-3 sm:flex-row">
            {data.primary_url && data.primary_label && <ButtonLink href={data.primary_url} variant="outline-light" className="!border-gold-light !text-gold-light hover:!bg-gold-light hover:!text-navy-deep">{data.primary_label}</ButtonLink>}
            {data.secondary_url && data.secondary_label && <ButtonLink href={data.secondary_url} variant="outline-light" className="!border-gold-light !text-gold-light hover:!bg-gold-light hover:!text-navy-deep">{data.secondary_label}</ButtonLink>}
          </Reveal>
        </div>
      </div>
    </section>
  );
}
