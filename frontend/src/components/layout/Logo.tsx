import Image from "next/image";
import Link from "next/link";
import { cn } from "@/lib/cn";
import type { MediaImage } from "@/lib/types";

type Props = {
  image?: MediaImage | null;
  inverse?: boolean;
  name: string;
  className?: string;
  /** `full` adds the “Business Consulting” descriptor (footer). */
  variant?: "wordmark" | "full";
};

/** CSINC91 logo; uses the CMS logo when set, falling back to the bundled brand files. */
export function Logo({ image, inverse = false, name, className, variant = "wordmark" }: Props) {
  const fallback = `/brand/csinc91-${variant === "full" ? "logo" : "wordmark"}${inverse ? "-inverse" : ""}.svg`;
  const src = variant === "full" ? fallback : (image?.url ?? fallback);
  const isSvg = src.endsWith(".svg");

  return (
    <Link href="/" aria-label={`${name} — home`} className={cn("inline-flex", className)}>
      <Image
        src={src}
        alt={name}
        width={variant === "full" ? 553 : (image?.width ?? 356)}
        height={image?.height && variant !== "full" ? image.height : 96}
        className={variant === "full" ? "h-11 w-auto md:h-12" : "h-[30px] w-auto lg:h-[36px]"}
        preload
        unoptimized={isSvg}
      />
    </Link>
  );
}
