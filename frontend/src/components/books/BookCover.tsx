import Image from "next/image";
import { cn } from "@/lib/cn";
import type { MediaImage } from "@/lib/types";

/**
 * Shows a product cover filling a 2:3 frame. Covers that are not exactly 2:3
 * are shown in full over a soft, blurred copy of themselves so the frame is
 * always filled and nothing is cropped.
 */
export function BookCover({
  cover,
  title,
  sizes,
  preload = false,
  className,
}: {
  cover: MediaImage | null;
  title: string;
  sizes: string;
  preload?: boolean;
  className?: string;
}) {
  const ratio = cover?.width && cover?.height ? cover.width / cover.height : 2 / 3;
  const exact = Math.abs(ratio - 2 / 3) < 0.02;

  return (
    <div className={cn("relative aspect-[2/3] overflow-hidden bg-navy shadow-[0_1px_2px_rgba(0,28,53,0.12),0_12px_32px_-14px_rgba(0,28,53,0.45)]", className)}>
      {cover ? (
        <>
          {!exact && (
            <Image src={cover.url} alt="" aria-hidden="true" fill sizes="16px" quality={40} className="scale-110 object-cover opacity-80" />
          )}
          <Image
            src={cover.url}
            alt={cover.alt || `${title} cover`}
            fill
            sizes={sizes}
            preload={preload}
            className={cn("transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-[1.03]", exact ? "object-cover" : "object-contain")}
          />
        </>
      ) : (
        <div className="flex h-full w-full items-end p-6">
          <span className="font-serif text-xl leading-tight text-white">{title}</span>
        </div>
      )}
    </div>
  );
}
