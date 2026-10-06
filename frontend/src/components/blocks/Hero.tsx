import type { BlockMap } from "@/lib/types";
import { cn } from "@/lib/cn";
import { Accent } from "@/components/ui/Accent";
import { BreadcrumbBar, type Crumb } from "@/components/ui/Breadcrumbs";
import { ButtonLink } from "@/components/ui/Button";
import { CmsImage } from "@/components/ui/CmsImage";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

type Props = { data: BlockMap["hero"]; breadcrumbs?: Crumb[]; isFirst?: boolean };

export function Hero({ data, breadcrumbs, isFirst = true }: Props) {
  const variant = data.variant ?? "split";
  const HeadingTag = isFirst ? "h1" : "h2";

  if (variant === "text") {
    return (
      <>
        {breadcrumbs && <BreadcrumbBar items={breadcrumbs} />}
        <section className="bg-white">
          <div className="container-site pb-14 pt-12 md:pb-20 md:pt-16">
            <Reveal immediate={isFirst} className="max-w-4xl">
              {data.eyebrow && <Eyebrow edge className="mb-5">{data.eyebrow}</Eyebrow>}
              <HeadingTag className="font-serif text-h1 text-navy">
                <Accent text={data.heading} />
              </HeadingTag>
              {data.body && <p className="mt-6 max-w-2xl text-lead text-muted">{data.body}</p>}
            </Reveal>
            <HeroActions data={data} tone="dark" immediate={isFirst} />
          </div>
        </section>
      </>
    );
  }

  if (variant === "full") {
    return (
      <>
        {breadcrumbs && <BreadcrumbBar items={breadcrumbs} />}
        <section className="relative isolate flex min-h-[25rem] items-center overflow-hidden bg-navy-deep text-white md:min-h-[30rem]">
          <CmsImage image={data.image} sizes="100vw" preload={isFirst} className="-z-20" quality={70} />
          <div aria-hidden="true" className="absolute inset-0 -z-10 bg-gradient-to-r from-navy-deep/90 via-navy-deep/60 to-navy-deep/10" />
          <div className="container-site w-full py-16">
            <Reveal immediate={isFirst} className="max-w-2xl">
              {data.eyebrow && <Eyebrow edge tone="light" className="mb-5">{data.eyebrow}</Eyebrow>}
              <HeadingTag className="font-serif text-h1 [&_em]:text-gold-light">
                <Accent text={data.heading} />
              </HeadingTag>
              {data.body && <p className="mt-6 text-lead text-white/85">{data.body}</p>}
            </Reveal>
            <HeroActions data={data} tone="light" immediate={isFirst} />
          </div>
        </section>
      </>
    );
  }

  return (
    <>
      {breadcrumbs && <BreadcrumbBar items={breadcrumbs} />}
      <section className="relative overflow-hidden bg-white">
        <div className="grid lg:min-h-[32.5rem] lg:grid-cols-12">
          <div className="container-site relative z-10 flex flex-col justify-center py-12 sm:py-16 lg:col-span-5 lg:py-16 lg:pr-10">
            <Reveal immediate={isFirst}>
              {data.eyebrow && <Eyebrow edge className="mb-5">{data.eyebrow}</Eyebrow>}
              <HeadingTag className="font-serif text-h1 text-navy">
                <Accent text={data.heading} />
              </HeadingTag>
              {data.body && <p className="mt-6 max-w-xl text-lead text-muted">{data.body}</p>}
            </Reveal>
            <HeroActions data={data} tone="dark" immediate={isFirst} />
          </div>
          <div className="relative overflow-hidden bg-navy lg:col-span-7">
            <div className="absolute inset-0">
              <CmsImage image={data.image} sizes="24px" className="scale-110" quality={40} />
              <div className="absolute inset-0 bg-navy/30" />
            </div>
            <div className="relative px-[clamp(1.5625rem,6vw,5.5rem)] py-10 sm:py-14 lg:py-[3.25rem]">
              <Reveal variant="mask" immediate={isFirst} className="relative aspect-[16/10]">
                <div className="reveal-scale absolute inset-0">
                  <CmsImage image={data.image} sizes="(min-width: 1024px) 45vw, 90vw" preload={isFirst} />
                </div>
              </Reveal>
              <span aria-hidden="true" className="absolute right-[clamp(0.75rem,4vw,3.75rem)] top-[clamp(1.25rem,3vw,2rem)] h-1/3 w-1/4 border-r-[6px] border-t-[6px] border-gold-light" />
              <span aria-hidden="true" className="absolute bottom-[clamp(1.25rem,3vw,2rem)] left-[clamp(0.75rem,4vw,3.75rem)] h-1/3 w-1/4 border-b-[6px] border-l-[6px] border-gold-light" />
            </div>
          </div>
        </div>
      </section>
    </>
  );
}

function HeroActions({ data, tone, immediate = false }: { data: BlockMap["hero"]; tone: "light" | "dark"; immediate?: boolean }) {
  if (!data.primary_url && !data.secondary_url) {
    return null;
  }

  return (
    <Reveal immediate={immediate} delay={150} className={cn("mt-9 flex flex-col gap-3 sm:flex-row sm:flex-wrap")}>
      {data.primary_url && data.primary_label && (
        <ButtonLink href={data.primary_url} variant={tone === "light" ? "outline-light" : "outline"}>
          {data.primary_label}
        </ButtonLink>
      )}
      {data.secondary_url && data.secondary_label && (
        <ButtonLink href={data.secondary_url} variant={tone === "light" ? "outline-light" : "outline"}>
          {data.secondary_label}
        </ButtonLink>
      )}
    </Reveal>
  );
}
