import { cn } from "@/lib/cn";
import type { MediaImage } from "@/lib/types";
import { CmsImage } from "./CmsImage";
import { Reveal } from "./Reveal";

/**
 * Editorial frame: a softly blurred copy of the photo fills the panel, the
 * photo sits inset with rounded corners. `accent` is accepted for compatibility.
 */
export function FramedImage({ image, className, sizes = "(min-width: 1024px) 45vw, 100vw" }: { image?: MediaImage | null; className?: string; accent?: string; sizes?: string }) {
  return (
    <div className={cn("relative aspect-square overflow-hidden rounded-[1.75rem] bg-navy-deep", className)}>
      <div className="absolute inset-0">
        <CmsImage image={image} sizes="24px" quality={40} className="scale-110 opacity-80" />
      </div>
      <Reveal variant="mask" className="absolute inset-[9%] overflow-hidden rounded-2xl shadow-[0_30px_60px_-30px_rgba(0,0,0,0.6)]">
        <div className="reveal-scale absolute inset-0">
          <CmsImage image={image} sizes={sizes} />
        </div>
      </Reveal>
    </div>
  );
}
