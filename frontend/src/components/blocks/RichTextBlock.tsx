import type { BlockMap } from "@/lib/types";
import { RichText } from "@/components/ui/RichText";

export function RichTextBlock({ data, aside }: { data: BlockMap["rich_text"]; aside?: React.ReactNode }) {
  return (
    <section className="py-16 md:py-24">
      <div className="container-site grid gap-12 lg:grid-cols-12">
        <div className="lg:col-span-8">
          <RichText html={data.body_html} className="max-w-3xl" />
        </div>
        {aside && <aside className="lg:col-span-3 lg:col-start-10">{aside}</aside>}
      </div>
    </section>
  );
}
