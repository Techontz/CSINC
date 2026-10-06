"use client";

import Image from "next/image";
import { useCallback, useEffect, useRef, useState } from "react";
import { cn } from "@/lib/cn";
import type { BlockMap, HeroSlide } from "@/lib/types";
import { Accent } from "@/components/ui/Accent";
import { ArrowUpRight } from "@/components/ui/Icons";
import { SmartLink } from "@/components/ui/SmartLink";

/** Phones and data-saver connections get posters instead of video. */
function useLightweightMedia(): boolean {
  const [light, setLight] = useState(true);

  useEffect(() => {
    const query = window.matchMedia("(max-width: 767px)");
    const saveData = (navigator as Navigator & { connection?: { saveData?: boolean } }).connection?.saveData === true;
    // eslint-disable-next-line react-hooks/set-state-in-effect -- read device characteristics after mount
    setLight(query.matches || saveData);
    const onChange = () => setLight(query.matches || saveData);
    query.addEventListener("change", onChange);
    return () => query.removeEventListener("change", onChange);
  }, []);

  return light;
}

function usePrefersReducedMotion(): boolean {
  const [reduced, setReduced] = useState(false);

  useEffect(() => {
    const query = window.matchMedia("(prefers-reduced-motion: reduce)");
    // eslint-disable-next-line react-hooks/set-state-in-effect -- read the user preference after mount
    setReduced(query.matches);
    const onChange = () => setReduced(query.matches);
    query.addEventListener("change", onChange);
    return () => query.removeEventListener("change", onChange);
  }, []);

  return reduced;
}

/**
 * Full-bleed rotating hero: each slide plays a muted looping background video
 * behind a large serif statement, with progress-bar navigation and a pause
 * control. Falls back to poster images when motion is reduced.
 */
export function HeroSlider({ data }: { data: BlockMap["hero_slider"] }) {
  const slides = data.slides.filter((slide) => slide.heading);
  const interval = Math.min(20, Math.max(4, Number(data.interval) || 7)) * 1000;
  const reducedMotion = usePrefersReducedMotion();
  const lightweight = useLightweightMedia();
  const [active, setActive] = useState(0);
  // Slides mount their media just before they are shown.
  const [mounted, setMounted] = useState<Set<number>>(() => new Set([0]));
  const [paused, setPaused] = useState(false);
  const videos = useRef<(HTMLVideoElement | null)[]>([]);

  const autoplay = !paused && !reducedMotion && slides.length > 1;

  const goTo = useCallback((index: number) => setActive((index + slides.length) % slides.length), [slides.length]);

  // Advance slides.
  useEffect(() => {
    if (!autoplay) {
      return;
    }
    const timer = setTimeout(() => goTo(active + 1), interval);
    return () => clearTimeout(timer);
  }, [active, autoplay, interval, goTo]);

  useEffect(() => {
    // eslint-disable-next-line react-hooks/set-state-in-effect -- extend the set of slides whose media is loaded
    setMounted((current) => {
      const next = (active + 1) % Math.max(slides.length, 1);
      if (current.has(active) && current.has(next)) {
        return current;
      }
      return new Set([...current, active, next]);
    });
  }, [active, slides.length]);

  // Only the visible slide's video plays.
  useEffect(() => {
    videos.current.forEach((video, index) => {
      if (!video) {
        return;
      }
      if (index === active && !paused && !reducedMotion) {
        video.play().catch(() => undefined);
      } else {
        video.pause();
      }
    });
  }, [active, paused, reducedMotion]);

  // Stop work while the tab is hidden.
  useEffect(() => {
    const onVisibility = () => {
      const video = videos.current[active];
      if (document.hidden) {
        video?.pause();
      } else if (!paused && !reducedMotion) {
        video?.play().catch(() => undefined);
      }
    };
    document.addEventListener("visibilitychange", onVisibility);
    return () => document.removeEventListener("visibilitychange", onVisibility);
  }, [active, paused, reducedMotion]);

  if (slides.length === 0) {
    return null;
  }

  return (
    <section
      aria-roledescription="carousel"
      aria-label="Highlights"
      className="relative isolate h-[min(43.75rem,calc(100svh-4.5rem))] lg:h-[min(43.75rem,calc(100svh-4.6875rem))] min-h-[34rem] overflow-hidden bg-navy-deep text-white"
      onKeyDown={(event) => {
        if (event.key === "ArrowRight") goTo(active + 1);
        if (event.key === "ArrowLeft") goTo(active - 1);
      }}
    >
      {slides.map((slide, index) =>
        mounted.has(index) ? (
        <SlideBackground
          key={index}
          slide={slide}
          visible={index === active}
          eager={index === 0}
          reducedMotion={reducedMotion || lightweight}
          kenBurns={lightweight && !reducedMotion && index === active}
          duration={interval}
          videoRef={(element) => {
            videos.current[index] = element;
          }}
        />
        ) : null,
      )}

      {/* Legibility: deep navy wash from the left and along the bottom. */}
      <div aria-hidden="true" className="absolute inset-0 -z-10 bg-navy-deep/55 md:bg-transparent md:bg-gradient-to-r md:from-navy-deep/85 md:via-navy-deep/45 md:to-transparent" />
      <div aria-hidden="true" className="absolute inset-x-0 bottom-0 -z-10 h-1/3 bg-gradient-to-t from-navy-deep/70 to-transparent" />

      <div className="container-site relative flex h-full flex-col justify-center pb-24 pt-8">
        {slides.map((slide, index) => (
          <SlideContent key={index} slide={slide} visible={index === active} isFirst={index === 0} position={index + 1} total={slides.length} />
        ))}
      </div>

      {slides.length > 1 && (
        <div className="container-site absolute inset-x-0 bottom-8 flex items-center gap-2 md:bottom-12">
          {slides.map((slide, index) => (
            <button
              key={index}
              type="button"
              onClick={() => goTo(index)}
              aria-label={`Show slide ${index + 1} of ${slides.length}`}
              aria-current={index === active}
              className="group relative h-6 w-10 sm:w-[3.75rem]"
            >
              <span className="absolute inset-x-0 top-1/2 h-[3px] -translate-y-1/2 bg-white/35 transition-colors group-hover:bg-white/60" />
              <span
                key={index === active ? `${active}-${paused}` : undefined}
                className={cn(
                  "absolute left-0 top-1/2 h-[3px] -translate-y-1/2 bg-white",
                  index === active ? (autoplay ? "hero-progress" : "w-full") : "w-0",
                )}
                style={index === active && autoplay ? { animationDuration: `${interval}ms` } : undefined}
              />
            </button>
          ))}
          <button
            type="button"
            onClick={() => setPaused((value) => !value)}
            aria-label={paused ? "Play slideshow" : "Pause slideshow"}
            className="ml-2 flex h-[1.375rem] w-[1.375rem] items-center justify-center rounded-full border border-white/80 text-white transition-colors hover:bg-white hover:text-navy"
          >
            {paused ? (
              <svg width="8" height="9" viewBox="0 0 8 9" fill="currentColor" aria-hidden="true"><path d="M0 0l8 4.5L0 9z" /></svg>
            ) : (
              <svg width="8" height="9" viewBox="0 0 8 9" fill="currentColor" aria-hidden="true"><path d="M0 0h2.5v9H0zM5.5 0H8v9H5.5z" /></svg>
            )}
          </button>
        </div>
      )}
    </section>
  );
}

function SlideBackground({
  slide,
  visible,
  eager,
  reducedMotion,
  kenBurns,
  duration,
  videoRef,
}: {
  slide: HeroSlide;
  visible: boolean;
  eager: boolean;
  reducedMotion: boolean;
  kenBurns: boolean;
  duration: number;
  videoRef: (element: HTMLVideoElement | null) => void;
}) {
  return (
    <div aria-hidden="true" className={cn("absolute inset-0 -z-20 transition-opacity duration-[1200ms] ease-out", visible ? "opacity-100" : "opacity-0")}>
      {slide.image && (
        <Image
          src={slide.image.url}
          alt=""
          fill
          sizes="100vw"
          preload={eager}
          fetchPriority={eager ? "high" : "auto"}
          quality={65}
          className={cn("object-cover", kenBurns && "hero-kenburns")}
          style={kenBurns ? { animationDuration: `${duration + 1200}ms` } : undefined}
        />
      )}
      {slide.video && !reducedMotion && (
        <video
          ref={videoRef}
          className="absolute inset-0 h-full w-full object-cover"
          src={slide.video.url}
          poster={slide.image?.url}
          muted
          loop
          playsInline
          disablePictureInPicture
          preload={eager ? "auto" : "metadata"}
          autoPlay={eager}
        />
      )}
    </div>
  );
}

function SlideContent({ slide, visible, isFirst, position, total }: { slide: HeroSlide; visible: boolean; isFirst: boolean; position: number; total: number }) {
  const Heading = isFirst ? "h1" : "h2";

  return (
    <div
      role="group"
      aria-roledescription="slide"
      aria-label={`${position} of ${total}`}
      aria-hidden={!visible}
      className={cn(
        "max-w-[40rem] transition-[opacity,transform] duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]",
        visible ? "relative translate-y-0 opacity-100 delay-300" : "pointer-events-none absolute translate-y-4 opacity-0",
      )}
    >
      <p className="mb-5 flex items-center gap-3 text-xs font-bold uppercase tracking-[0.2em] text-gold-light md:text-[0.8125rem]">
        <span className="tabular-nums">{String(position).padStart(2, "0")}</span>
        <span aria-hidden="true" className="h-px w-8 bg-gold-light/70" />
        <span>{slide.eyebrow ?? `${position} of ${total}`}</span>
      </p>
      <Heading className="font-serif text-[clamp(1.875rem,1.3rem+2.6vw,3.75rem)] text-balance [overflow-wrap:anywhere] font-medium leading-[1.08] tracking-[-0.01em] [&_em]:text-gold-light">
        <Accent text={slide.heading} />
      </Heading>
      {slide.body && <p className="mt-5 max-w-[32rem] text-pretty text-[1.0625rem] font-light leading-relaxed text-white/85 md:text-[1.1875rem]">{slide.body}</p>}
      {slide.link_url && slide.link_label && (
        <SmartLink
          href={slide.link_url}
          tabIndex={visible ? 0 : -1}
          className="group mt-8 inline-flex items-center gap-3 border border-gold-light px-[0.8125rem] py-[0.8125rem] text-sm font-bold uppercase tracking-[0.1em] text-gold-light transition-colors hover:bg-gold-light hover:text-navy-deep"
        >
          {slide.link_label}
          <ArrowUpRight size={16} strokeWidth={2.25} className="arrow-shift" />
        </SmartLink>
      )}
    </div>
  );
}
