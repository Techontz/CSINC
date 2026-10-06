import type { BlockMap } from "@/lib/types";
import { Accent } from "@/components/ui/Accent";
import { ArrowLink } from "@/components/ui/Button";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

export function Statement({ data }: { data: BlockMap["statement"] }) {
  return (
    <section className="section-y bg-white">
      <div className="container-site grid gap-10 lg:grid-cols-12">
        <Reveal className="lg:col-span-3">{data.eyebrow && <Eyebrow>{data.eyebrow}</Eyebrow>}</Reveal>
        <div className="lg:col-span-9">
          <Reveal>
            <p className="font-serif text-h2 leading-[1.25] text-navy [&_em]:text-gold">
              <Accent text={data.text} />
            </p>
          </Reveal>
          {data.link_url && data.link_label && (
            <Reveal delay={120} className="mt-12">
              <ArrowLink href={data.link_url}>{data.link_label}</ArrowLink>
            </Reveal>
          )}
        </div>
      </div>
    </section>
  );
}
