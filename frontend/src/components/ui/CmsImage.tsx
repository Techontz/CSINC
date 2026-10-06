import Image from "next/image";
import { cn } from "@/lib/cn";
import type { MediaImage } from "@/lib/types";

type Props = {
  image: MediaImage | null | undefined;
  sizes: string;
  className?: string;
  preload?: boolean;
  alt?: string;
  quality?: number;
};

/** Fills its (relatively positioned) parent with an optimised CMS image. */
export function CmsImage({ image, sizes, className, preload = false, alt, quality = 75 }: Props) {
  if (!image) {
    return <div className={cn("absolute inset-0 bg-gradient-to-br from-navy to-navy-deep", className)} aria-hidden="true" />;
  }

  return (
    <Image
      src={image.url}
      alt={alt ?? image.alt ?? ""}
      fill
      sizes={sizes}
      preload={preload}
      quality={quality}
      className={cn("object-cover", className)}
    />
  );
}
