import type { BlockMap } from "@/lib/types";
import { cn } from "@/lib/cn";
import { Accent } from "@/components/ui/Accent";
import { ArrowLink } from "@/components/ui/Button";
import { CmsImage } from "@/components/ui/CmsImage";
import { RichText } from "@/components/ui/RichText";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

export function Split({ data }: { data: BlockMap["split"] }) {
  const dark = data.theme === "dark";
  const imageLeft = data.image_position === "left";

  return (
    <section className={cn("section-y overflow-hidden", dark ? "bg-navy text-white" : "bg-white")}>
      <div className="container-site grid items-center gap-12 lg:grid-cols-12 lg:gap-20">
        <div className={cn("relative isolate lg:col-span-6", imageLeft ? "lg:order-1" : "lg:order-2")}>
          <Reveal variant="mask" className="relative aspect-[4/5] overflow-hidden sm:aspect-[4/3] lg:aspect-[4/5]">
            <div className="reveal-scale absolute inset-0">
              <CmsImage image={data.image} sizes="(min-width: 1024px) 45vw, 100vw" />
            </div>
          </Reveal>
          <div aria-hidden="true" className={cn("absolute -bottom-3 -z-0 h-full w-full", dark ? "bg-gold-light" : "bg-gold", imageLeft ? "-left-3" : "-right-3")} style={{ zIndex: -1 }} />
        </div>
        <Reveal className={cn("lg:col-span-5", imageLeft ? "lg:order-2 lg:col-start-8" : "lg:order-1")} delay={120}>
          {data.eyebrow && <Eyebrow tone={dark ? "light" : "dark"} className="mb-7">{data.eyebrow}</Eyebrow>}
          <h2 className={cn("font-serif text-h2", dark ? "text-white [&_em]:text-gold-light" : "text-navy")}>
            <Accent text={data.heading} />
          </h2>
          <RichText html={data.body_html} invert={dark} className="mt-8" />
          {data.link_url && data.link_label && (
            <div className="mt-10">
              <ArrowLink href={data.link_url} tone={dark ? "light" : "dark"}>{data.link_label}</ArrowLink>
            </div>
          )}
        </Reveal>
      </div>
    </section>
  );
}
