import type { BlockMap } from "@/lib/types";
import { Accent } from "@/components/ui/Accent";
import { JsonLd } from "@/components/ui/JsonLd";
import { Plus } from "@/components/ui/Icons";
import { RichText } from "@/components/ui/RichText";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";

export function Faq({ data }: { data: BlockMap["faq"] }) {
  return (
    <section className="section-y bg-white">
      <JsonLd
        data={{
          "@context": "https://schema.org",
          "@type": "FAQPage",
          mainEntity: data.items.map((item) => ({
            "@type": "Question",
            name: item.question,
            acceptedAnswer: { "@type": "Answer", text: (item.answer ?? "").replace(/<[^>]+>/g, "") },
          })),
        }}
      />
      <div className="container-site grid gap-14 lg:grid-cols-12">
        <Reveal className="lg:col-span-4">
          <div className="lg:sticky lg:top-[7rem]">
            {data.eyebrow && <Eyebrow className="mb-6">{data.eyebrow}</Eyebrow>}
            <h2 className="font-serif text-h2 text-navy">
              <Accent text={data.heading} />
            </h2>
          </div>
        </Reveal>
        <div className="lg:col-span-7 lg:col-start-6">
          {data.items.map((item, index) => (
            <Reveal key={item.question} delay={index * 60}>
              <details className="group border-b border-line [&_summary::-webkit-details-marker]:hidden" open={index === 0}>
                <summary className="flex cursor-pointer list-none items-start justify-between gap-6 py-7 text-left">
                  <span className="font-serif text-[1.35rem] leading-snug text-navy transition-colors group-hover:text-gold md:text-[1.55rem]">{item.question}</span>
                  <span className="mt-1 flex h-9 w-9 shrink-0 items-center justify-center border border-navy/15 text-navy transition-transform duration-300 group-open:rotate-45">
                    <Plus />
                  </span>
                </summary>
                <RichText html={item.answer} className="max-w-2xl pb-8 pr-12 text-muted" />
              </details>
            </Reveal>
          ))}
        </div>
      </div>
    </section>
  );
}
