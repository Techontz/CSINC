"use client";

import { useState } from "react";
import type { BlockMap } from "@/lib/types";
import { cn } from "@/lib/cn";
import { Accent } from "@/components/ui/Accent";
import { ArrowLink } from "@/components/ui/Button";
import { CmsImage } from "@/components/ui/CmsImage";
import { ArrowRight, ArrowUpRight } from "@/components/ui/Icons";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { Reveal } from "@/components/ui/Reveal";
import { SmartLink } from "@/components/ui/SmartLink";

/** “Results” carousel: a large navy figure card with offset accent blocks and a next control. */
export function Results({ data }: { data: BlockMap["results"] }) {
  const [active, setActive] = useState(0);
  const items = data.items;
  const go = (step: number) => setActive((current) => (current + step + items.length) % items.length);

  return (
    <section className="section-y overflow-hidden bg-white">
      <div className="container-site grid items-center gap-12 lg:grid-cols-12 lg:gap-16">
        <Reveal className="lg:col-span-4">
          {data.eyebrow && <Eyebrow className="mb-4">{data.eyebrow}</Eyebrow>}
          <h2 className="font-serif text-h2 text-navy">
            <Accent text={data.heading} />
          </h2>
          {data.link_url && data.link_label && <ArrowLink href={data.link_url} className="mt-8">{data.link_label}</ArrowLink>}
        </Reveal>

        <div className="relative lg:col-span-8 lg:pl-6">
          <span aria-hidden="true" className="absolute -top-4 right-0 h-24 w-1/4 bg-gold md:-top-5" />
          <span aria-hidden="true" className="absolute -bottom-4 left-0 h-24 w-1/5 bg-gold md:-bottom-5 lg:left-6" />
          <div className="relative min-h-[22rem] overflow-hidden bg-navy-deep md:min-h-[26rem]" aria-roledescription="carousel" aria-label={data.eyebrow ?? "Highlights"}>
            {items.map((item, index) => {
              const content = (
                <>
                  <CmsImage image={item.image} sizes="(min-width: 1024px) 55vw, 100vw" className="opacity-35" quality={60} />
                  <div aria-hidden="true" className="absolute inset-0 bg-gradient-to-r from-navy-deep via-navy-deep/85 to-navy-deep/40" />
                  <div className="relative flex h-full flex-col justify-center px-8 py-12 md:px-16">
                    {item.eyebrow && <p className="eyebrow text-gold-light">{item.eyebrow}</p>}
                    <p className="mt-4 font-serif text-[clamp(4rem,3rem+4vw,6.5rem)] leading-none text-white">{item.value}</p>
                    <p className="mt-5 max-w-md text-[clamp(1.125rem,1rem+0.6vw,1.5rem)] font-light leading-snug text-white/90">{item.label}</p>
                    {item.url && (
                      <span className="mt-7 inline-flex items-center gap-2 text-sm font-bold uppercase tracking-[0.1em] text-gold-light">
                        Explore <ArrowUpRight size={16} strokeWidth={2.25} className="arrow-shift" />
                      </span>
                    )}
                  </div>
                </>
              );

              return (
                <div
                  key={index}
                  aria-hidden={index !== active}
                  className={cn("absolute inset-0 transition-opacity duration-700", index === active ? "z-10 opacity-100" : "pointer-events-none opacity-0")}
                >
                  {item.url ? (
                    <SmartLink href={item.url} tabIndex={index === active ? 0 : -1} className="group block h-full">{content}</SmartLink>
                  ) : (
                    <div className="h-full">{content}</div>
                  )}
                </div>
              );
            })}
          </div>
          {items.length > 1 && (
            <>
              <button
                type="button"
                onClick={() => go(1)}
                aria-label="Next highlight"
                className="absolute right-3 top-1/2 z-20 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white text-navy shadow-lg transition-transform hover:scale-105 md:-right-6"
              >
                <ArrowRight size={20} strokeWidth={2} />
              </button>
              <div className="relative z-10 mt-10 flex justify-end gap-2">
                {items.map((_, index) => (
                  <button
                    key={index}
                    type="button"
                    onClick={() => setActive(index)}
                    aria-label={`Show highlight ${index + 1}`}
                    aria-current={index === active}
                    className={cn("h-[3px] w-10 transition-colors", index === active ? "bg-navy" : "bg-navy/20 hover:bg-navy/40")}
                  />
                ))}
              </div>
            </>
          )}
        </div>
      </div>
    </section>
  );
}
