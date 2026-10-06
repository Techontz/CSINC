import type { Metadata } from "next";
import { ResendDownloadsForm } from "@/components/cart/ResendDownloadsForm";
import { BreadcrumbBar } from "@/components/ui/Breadcrumbs";
import { Eyebrow } from "@/components/ui/SectionHeading";
import { getSite } from "@/lib/api";
import { buildMetadata } from "@/lib/seo";

export async function generateMetadata(): Promise<Metadata> {
  const site = await getSite();
  return buildMetadata(site, {
    title: "My downloads",
    description: "Get fresh download links for the CSinc91 guides you have purchased.",
    path: "/downloads",
  });
}

export default function DownloadsPage() {
  return (
    <>
    <BreadcrumbBar items={[{ label: "My downloads", href: "/downloads" }]} />
    <section className="min-h-[70vh] bg-white">
      <div className="container-site grid gap-14 py-14 md:py-20 lg:grid-cols-12">
        <div className="lg:col-span-6">
          <Eyebrow edge className="mb-5">Purchased resources</Eyebrow>
          <h1 className="font-serif text-h1 text-navy">
            Access your <em className="accent">downloads</em>
          </h1>
          <p className="mt-7 max-w-lg text-lead text-muted">
            Enter the email address you used at checkout and we will send fresh, secure links to every guide you have purchased.
          </p>
        </div>
        <div className="lg:col-span-5 lg:col-start-8 lg:pt-10">
          <ResendDownloadsForm />
        </div>
      </div>
    </section>
    </>
  );
}
