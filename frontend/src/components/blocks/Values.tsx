import type { BlockMap } from "@/lib/types";
import { SectionHeading } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

export function Values({ data }: { data: BlockMap["values"] }) {
  return (
    <section className="section-y bg-paper">
      <div className="container-site">
        <SectionHeading eyebrow={data.eyebrow} heading={data.heading} />
        <ul className="mt-16 grid gap-12 md:mt-24 md:grid-cols-3 md:gap-10">
          {data.items.map((item, index) => (
            <Reveal as="li" key={item.label} delay={index * 120} className="border-t border-navy/20 pt-8">
              <p className="eyebrow text-gold-ink">{item.label}</p>
              <p className="mt-5 text-h3 font-normal text-navy">{item.body}</p>
            </Reveal>
          ))}
        </ul>
      </div>
    </section>
  );
}
