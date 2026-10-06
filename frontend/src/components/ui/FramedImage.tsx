import { cn } from "@/lib/cn";
import type { MediaImage } from "@/lib/types";
import { CmsImage } from "./CmsImage";
import { Reveal } from "./Reveal";

/**
 * Editorial frame: a softly blurred copy of the photo fills the panel, the
 * photo sits inset, and accent brackets mark two opposite corners.
 */
export function FramedImage({ image, className, accent = "border-gold-light", sizes = "(min-width: 1024px) 45vw, 100vw" }: { image?: MediaImage | null; className?: string; accent?: string; sizes?: string }) {
  return (
    <div className={cn("relative aspect-square overflow-hidden bg-navy-deep", className)}>
      <div className="absolute inset-0">
        <CmsImage image={image} sizes="24px" quality={40} className="scale-110 opacity-80" />
      </div>
      <Reveal variant="mask" className="absolute inset-[13%]">
        <div className="reveal-scale absolute inset-0">
          <CmsImage image={image} sizes={sizes} />
        </div>
      </Reveal>
      <span aria-hidden="true" className={cn("absolute right-[9%] top-[9%] h-[30%] w-[30%] border-r-[6px] border-t-[6px]", accent)} />
      <span aria-hidden="true" className={cn("absolute bottom-[9%] left-[9%] h-[30%] w-[30%] border-b-[6px] border-l-[6px]", accent)} />
    </div>
  );
}
