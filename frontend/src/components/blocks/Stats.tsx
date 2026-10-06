import type { BlockMap } from "@/lib/types";
import { ArrowLink } from "@/components/ui/Button";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

export function Stats({ data }: { data: BlockMap["stats"] }) {
  return (
    <section className="section-y bg-paper">
      <div className="container-site">
        <SectionHeading
          eyebrow={data.eyebrow}
          heading={data.heading}
          action={data.link_url && data.link_label ? <ArrowLink href={data.link_url}>{data.link_label}</ArrowLink> : undefined}
        />
        <dl className="mt-16 grid gap-10 sm:grid-cols-2 md:mt-24 lg:grid-cols-3">
          {data.items.map((item, index) => (
            <Reveal key={item.label} delay={index * 120} className="border-t border-navy/20 pt-8">
              <dt className="sr-only">{item.label}</dt>
              <dd>
                <span className="block font-serif text-stat text-navy">{item.value}</span>
                <span className="mt-4 block max-w-xs text-h3 font-medium text-navy">{item.label}</span>
              </dd>
            </Reveal>
          ))}
        </dl>
      </div>
    </section>
  );
}
