export type MediaImage = {
  url: string;
  alt: string;
  width: number | null;
  height: number | null;
  mime_type?: string;
};

export type NavigationLink = {
  label: string;
  url: string;
  description: string | null;
  new_tab: boolean;
  children: NavigationLink[];
};

export type SocialLink = { platform: string; url: string };

export type SiteData = {
  name: string;
  legal_name: string;
  tagline: string;
  description: string;
  logo: MediaImage | null;
  logo_inverse: MediaImage | null;
  contact: {
    email: string;
    support_email: string | null;
    phone: string | null;
    address: {
      line_1: string | null;
      line_2: string | null;
      city: string | null;
      region: string | null;
      postal_code: string | null;
      country: string | null;
    };
    hours: string | null;
    consultation_url: string | null;
  };
  social: SocialLink[];
  seo: {
    title_template: string;
    default_title: string;
    default_description: string;
    image: MediaImage | null;
  };
  footer: { statement: string | null };
  commerce: { enabled: boolean; currency: string; checkout_note: string | null };
  navigation: { header: NavigationLink[]; footer: NavigationLink[]; legal: NavigationLink[] };
};

export type Price = {
  amount_cents: number;
  regular_cents: number;
  currency: string;
  formatted: string;
  formatted_regular: string;
  on_sale: boolean;
  is_free: boolean;
};

export type CategoryRef = { name: string; slug: string };

export type Book = {
  id: number;
  slug: string;
  title: string;
  subtitle: string | null;
  short_description: string | null;
  sku: string | null;
  format: string | null;
  cover: MediaImage | null;
  price: Price | null;
  categories: CategoryRef[];
  is_featured: boolean;
  is_purchasable: boolean;
  external_purchase_url: string | null;
  published_at: string | null;
  updated_at: string | null;
};

export type BookDetail = Book & {
  description: string | null;
  author: string | null;
  co_authors: string[];
  isbn: string | null;
  publisher: string | null;
  publication_date: string | null;
  edition: string | null;
  language: string | null;
  page_count: number | null;
  tags: CategoryRef[];
  gallery: MediaImage[];
  sample_url: string | null;
  has_download: boolean;
  seo: { title: string; description: string | null; image: MediaImage | null };
};

export type BookCategory = {
  id: number;
  name: string;
  slug: string;
  headline: string | null;
  description: string | null;
  image: MediaImage | null;
  books_count?: number;
  seo: { title: string; description: string | null };
};

export type Service = {
  id: number;
  slug: string;
  title: string;
  group: "business" | "healthcare" | "digital";
  group_label: string;
  summary: string | null;
  body: string | null;
  highlights: string[];
  image: MediaImage | null;
  link: { label: string; url: string } | null;
};

type Link = { link_label?: string | null; link_url?: string | null };

export type HeroSlide = {
  eyebrow?: string | null;
  heading: string;
  body?: string | null;
  link_label?: string | null;
  link_url?: string | null;
  video?: MediaImage | null;
  image?: MediaImage | null;
};

export type BlockMap = {
  hero_slider: { interval?: number | string | null; slides: HeroSlide[] };
  hero: {
    variant?: "split" | "full" | "text";
    eyebrow?: string | null;
    heading: string;
    body?: string | null;
    primary_label?: string | null;
    primary_url?: string | null;
    secondary_label?: string | null;
    secondary_url?: string | null;
    image?: MediaImage | null;
  };
  statement: { eyebrow?: string | null; text: string } & Link;
  pillars: Link & {
    eyebrow?: string | null;
    heading: string;
    items: ({ title: string; body?: string | null; image?: MediaImage | null } & Link)[];
  };
  services: {
    eyebrow?: string | null;
    heading: string;
    group?: string | null;
    layout?: "alternating" | "grid";
    services: Service[];
  };
  books: {
    eyebrow?: string | null;
    heading: string;
    intro?: string | null;
    source?: string;
    layout?: "carousel" | "grid";
    books: Book[];
  } & Link;
  book_categories: { eyebrow?: string | null; heading: string; categories: BookCategory[] };
  split: {
    eyebrow?: string | null;
    heading: string;
    body_html?: string | null;
    image?: MediaImage | null;
    image_position?: "left" | "right";
    theme?: "light" | "dark";
  } & Link;
  stats: { eyebrow?: string | null; heading: string; items: { value: string; label: string }[] } & Link;
  values: { eyebrow?: string | null; heading: string; items: { label: string; body: string }[] };
  faq: { eyebrow?: string | null; heading: string; items: { question: string; answer: string | null }[] };
  cta_band: {
    eyebrow?: string | null;
    heading: string;
    body?: string | null;
    primary_label?: string | null;
    primary_url?: string | null;
    secondary_label?: string | null;
    secondary_url?: string | null;
    background?: MediaImage | null;
    align?: "left" | "right";
  };
  service_links: { eyebrow?: string | null; heading: string; body?: string | null; image?: MediaImage | null; services: Service[] };
  results: {
    eyebrow?: string | null;
    heading: string;
    items: { eyebrow?: string | null; value: string; label: string; url?: string | null; image?: MediaImage | null }[];
  } & Link;
  rich_text: { body_html: string | null };
  contact: { heading?: string | null; intro?: string | null };
};

export type BlockType = keyof BlockMap;

export type PageBlock = { [K in BlockType]: { type: K; id: string; data: BlockMap[K] } }[BlockType];

export type PageData = {
  slug: string;
  title: string;
  eyebrow: string | null;
  summary: string | null;
  blocks: PageBlock[];
  seo: { title: string; description: string | null; image: MediaImage | null };
  updated_at: string | null;
};

export type Paginated<T> = {
  data: T[];
  meta: { current_page: number; last_page: number; per_page: number; total: number; from: number | null; to: number | null };
};

export type ConsultationOptions = {
  industries: string[];
  growth_stages: string[];
  challenges: string[];
  goals: string[];
  services_of_interest: string[];
  preferred_services: string[];
};

export type OrderData = {
  uuid: string;
  number: string;
  status: "pending" | "paid" | "failed" | "cancelled" | "refunded";
  status_label: string;
  customer_name: string;
  customer_email: string;
  total: string;
  created_at: string;
  paid_at: string | null;
  items: { title: string; slug: string | null; price: string; cover: string | null; download_url: string | null }[];
};
