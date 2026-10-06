<?php

namespace Database\Seeders;

use App\Enums\NavigationLocation;
use App\Enums\PageStatus;
use App\Enums\ServiceGroup;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Seeds CSinc91's existing website content (copy taken from csinc91.com) into
 * the CMS: pages composed of blocks, services, navigation and settings.
 */
class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->settings();
        $this->services();
        $this->pages();
        $this->navigation();
    }

    private function settings(): void
    {
        SiteSetting::putMany([
            'site.logo_id' => MediaSeeder::idFor('logo'),
            'site.logo_inverse_id' => MediaSeeder::idFor('logo-inverse'),
            'seo.og_image_id' => MediaSeeder::idFor('hero-signing'),
        ]);
    }

    private function services(): void
    {
        $services = [
            [
                'slug' => 'business-formation', 'group' => ServiceGroup::Business, 'title' => 'Business Formation', 'image' => 'service-formation',
                'summary' => 'Complete setup of your business entity — LLC, corporation, or nonprofit. Includes EIN registration, operating agreements, corporate bylaws, business banking setup, and all foundational documentation required to operate legally from day one.',
                'highlights' => ['LLC & Corp Setup', 'EIN Registration', 'Operating Agreements'],
            ],
            [
                'slug' => 'business-credit-development', 'group' => ServiceGroup::Business, 'title' => 'Business Credit Development', 'image' => 'service-business-credit',
                'summary' => 'Build a legitimate, fundable business credit profile completely separate from your personal credit — trade lines, vendor accounts, and a business credit file that positions your company to qualify for financing and higher limits over time.',
                'highlights' => ['Trade Line Strategy', 'Vendor Accounts', 'Funding Readiness'],
            ],
            [
                'slug' => 'personal-credit-consulting', 'group' => ServiceGroup::Business, 'title' => 'Personal Credit Consulting', 'image' => 'service-personal-credit',
                'summary' => 'Your personal credit score is the gateway to business funding. We dispute inaccurate reporting, navigate consumer protection rights, optimize your profile, and build a stronger score.',
                'highlights' => ['Dispute Strategy', 'Score Optimization', 'Consumer Rights'],
            ],
            [
                'slug' => 'business-restructure', 'group' => ServiceGroup::Business, 'title' => 'Business Restructure', 'image' => 'service-restructure',
                'summary' => 'Already operating but need a stronger foundation? We assess your structure, identify gaps in compliance, credit positioning, and operational efficiency, then rebuild the infrastructure needed for growth.',
                'highlights' => ['Entity Restructure', 'Compliance Audit', 'Scaling Strategy'],
            ],
            [
                'slug' => 'healthcare-business-services', 'group' => ServiceGroup::Healthcare, 'title' => 'Business Services', 'image' => 'pillar-business',
                'summary' => 'Entity formation, business & personal credit development, and business restructuring — the financial foundation your company needs to grow and qualify for funding.',
                'highlights' => ['RAL & Group Homes', 'Sober Living', 'Hospice'],
            ],
            [
                'slug' => 'healthcare-housing', 'group' => ServiceGroup::Healthcare, 'title' => 'Healthcare & Housing', 'image' => 'healthcare-care',
                'summary' => 'Specialized consulting for care-facility operators — assisted living, group homes, sober living, veterans housing, hospice, compliance & survey readiness.',
                'highlights' => ['Plan of Corrections', 'Pre-Survey Audit'],
            ],
            [
                'slug' => 'healthcare-digital-products', 'group' => ServiceGroup::Healthcare, 'title' => 'Digital Products', 'image' => 'pillar-products',
                'summary' => '{{products.count}} step-by-step startup blueprints — healthcare, residential care & general business — delivered as instant PDF downloads with practitioner support.',
                'highlights' => ['Business Plans', 'Grant Strategy'],
                'link_label' => 'Browse products', 'link_url' => '/products',
            ],
            [
                'slug' => 'program-design-client-acquisition', 'group' => ServiceGroup::Healthcare, 'title' => 'Program design & client acquisition', 'image' => 'healthcare-program',
                'summary' => 'Program descriptions, referral partner outreach, intake templates, census-building strategies, and facility one-pagers that fill beds and build community trust.',
                'highlights' => ['Referral Outreach', 'Intake Templates'],
            ],
            [
                'slug' => 'products-guided-resources', 'group' => ServiceGroup::Healthcare, 'title' => 'Products & guided resources', 'image' => 'healthcare-resources',
                'summary' => 'Step-by-step guides for RAL startup, sober living, veteran housing, childcare licensing, hospice, and plan of corrections.',
                'highlights' => ['Self-Guided', 'Actionable Templates'],
                'link_label' => 'Browse healthcare guides', 'link_url' => '/product-category/healthcare',
            ],
            [
                'slug' => 'website-development', 'group' => ServiceGroup::Digital, 'title' => 'Website Development', 'image' => 'website-development',
                'summary' => 'Tailored solutions designed to fit your business needs perfectly.',
                'highlights' => ['Professional Websites', 'Smart Automations', 'System Integrations'],
            ],
            [
                'slug' => 'automation-marketing-solutions', 'group' => ServiceGroup::Digital, 'title' => 'Automation and Marketing Solutions', 'image' => 'team-laptops',
                'summary' => 'We design compelling company profiles, branding materials, and social media marketing strategies that strengthen your presence, build credibility, and drive sustainable business growth.',
                'highlights' => ['Company Profiles', 'Branding Materials', 'Social Media Marketing'],
            ],
        ];

        foreach ($services as $index => $service) {
            Service::query()->updateOrCreate(['slug' => $service['slug']], [
                'title' => $service['title'],
                'group' => $service['group'],
                'summary' => $service['summary'],
                'highlights' => $service['highlights'],
                'image_id' => MediaSeeder::idFor($service['image']),
                'link_label' => $service['link_label'] ?? null,
                'link_url' => $service['link_url'] ?? null,
                'sort_order' => $index + 1,
                'is_published' => true,
            ]);
        }
    }

    private function pages(): void
    {
        $automationCopy = '<p>We build professional websites, implement smart automations, and integrate your systems to create seamless, efficient operations. Alongside this, we design compelling company profiles, branding materials, and social media marketing strategies that strengthen your presence, build credibility, and drive sustainable business growth.</p><p>By combining technology, design, and strategic marketing, we help businesses streamline workflows, improve customer engagement, and create a stronger digital presence. Our solutions are tailored to support long-term success, allowing you to focus on growing your business while we handle the technical and creative aspects behind the scenes.</p>';

        $contactBand = $this->block('cta_band', [
            'eyebrow' => 'Contact us',
            'heading' => 'Get in touch',
            'body' => 'Have questions or need assistance? Get in touch with us today!',
            'primary_label' => 'Contact Us',
            'primary_url' => '/contact-us',
            'secondary_label' => 'Schedule a consultation',
            'secondary_url' => 'mailto:Info@CSinc91.com?subject=Consultation%20Request',
            'background_id' => MediaSeeder::idFor('strategy-session'),
        ]);

        $pages = [
            [
                'slug' => Page::HOME_SLUG,
                'title' => 'Home',
                'seo_title' => 'CSinc91 — Web Development, Business Automation and Digital Marketing',
                'seo_description' => 'Strategic consulting, startup blueprints, and done-for-you business infrastructure for entrepreneurs, care-facility operators, and small business owners at every stage.',
                'blocks' => [
                    $this->block('hero_slider', [
                        'interval' => 7,
                        'slides' => [
                            [
                                'eyebrow' => 'Business consulting',
                                'heading' => 'Transforming ideas into *scalable* business success',
                                'body' => 'Strategic consulting, startup blueprints, and done-for-you business infrastructure for entrepreneurs at every stage.',
                                'link_label' => 'Explore services',
                                'link_url' => '/services',
                                'video_id' => MediaSeeder::idFor('hero-video-signing'),
                                'image_id' => MediaSeeder::idFor('hero-poster-signing'),
                            ],
                            [
                                'eyebrow' => 'Business services',
                                'heading' => 'Building strong businesses from the *foundation* up',
                                'body' => 'Entity formation, business & personal credit development, and business restructuring.',
                                'link_label' => 'Business services',
                                'link_url' => '/services',
                                'video_id' => MediaSeeder::idFor('hero-video-business'),
                                'image_id' => MediaSeeder::idFor('hero-poster-business'),
                            ],
                            [
                                'eyebrow' => 'Healthcare & housing',
                                'heading' => 'Supporting the operators who care for the *most vulnerable*',
                                'body' => 'Consulting for assisted living, group homes, sober living, veterans housing and hospice operators.',
                                'link_label' => 'Explore healthcare',
                                'link_url' => '/healthcare',
                                'video_id' => MediaSeeder::idFor('hero-video-healthcare'),
                                'image_id' => MediaSeeder::idFor('hero-poster-healthcare'),
                            ],
                            [
                                'eyebrow' => 'Digital products',
                                'heading' => '{{products.count}} step-by-step *startup* blueprints',
                                'body' => 'Instant PDF downloads with practitioner support — healthcare, residential care & general business.',
                                'link_label' => 'Browse products',
                                'link_url' => '/products',
                                'video_id' => MediaSeeder::idFor('hero-video-digital'),
                                'image_id' => MediaSeeder::idFor('hero-poster-digital'),
                            ],
                        ],
                    ]),
                    $this->block('pillars', [
                        'eyebrow' => 'What we do',
                        'heading' => 'Three ways to *build* with us',
                        'link_label' => 'See all services',
                        'link_url' => '/services',
                        'items' => [
                            ['title' => 'Business Services', 'body' => 'Entity formation, business & personal credit development, and business restructuring — the financial foundation your company needs to grow and qualify for funding.', 'image_id' => MediaSeeder::idFor('pillar-business'), 'link_label' => 'Explore services', 'link_url' => '/services'],
                            ['title' => 'Healthcare & Housing', 'body' => 'Specialized consulting for care-facility operators — assisted living, group homes, sober living, veterans housing, hospice, compliance & survey readiness.', 'image_id' => MediaSeeder::idFor('residential-care-home'), 'link_label' => 'Explore healthcare', 'link_url' => '/healthcare'],
                            ['title' => 'Digital Products', 'body' => '{{products.count}} step-by-step startup blueprints — healthcare, residential care & general business — delivered as instant PDF downloads with practitioner support.', 'image_id' => MediaSeeder::idFor('team-laptops'), 'link_label' => 'Browse products', 'link_url' => '/products'],
                        ],
                    ]),
                    $this->block('service_links', [
                        'heading' => 'Building strong businesses from the *foundation up*',
                        'body' => 'From forming your first entity to restructuring an established operation — we provide the financial foundation, credit infrastructure, and strategic framework your business needs to grow.',
                        'image_id' => MediaSeeder::idFor('strategy-session'),
                    ]),
                    $this->block('results', [
                        'eyebrow' => 'Products & resources',
                        'heading' => 'Startup guides designed for *real-world* success',
                        'link_label' => 'Browse all products',
                        'link_url' => '/products',
                        'items' => [
                            ['eyebrow' => 'Healthcare & residential care', 'value' => '{{category.healthcare.count}}', 'label' => 'startup guides for assisted living, group homes, veterans housing, hospice & care training', 'url' => '/product-category/healthcare', 'image_id' => MediaSeeder::idFor('healthcare-program')],
                            ['eyebrow' => 'General business', 'value' => '{{category.general-business.count}}', 'label' => 'startup guides for financial, retail, transport, cleaning, trades & security businesses', 'url' => '/product-category/general-business', 'image_id' => MediaSeeder::idFor('service-formation')],
                            ['eyebrow' => 'Every guide', 'value' => '{{products.count}}', 'label' => 'step-by-step blueprints delivered as instant PDF downloads with practitioner support', 'url' => '/products', 'image_id' => MediaSeeder::idFor('hero-signing')],
                        ],
                    ]),
                    $this->block('books', [
                        'eyebrow' => 'Featured products',
                        'heading' => 'Startup blueprints for *care operators* and entrepreneurs',
                        'intro' => 'All products are instant PDF downloads and include practitioner support.',
                        'source' => 'featured',
                        'layout' => 'carousel',
                        'limit' => 10,
                        'link_label' => 'View all products',
                        'link_url' => '/products',
                    ]),
                    $this->block('cta_band', [
                        'align' => 'right',
                        'eyebrow' => 'Our mission',
                        'heading' => 'Committed to guiding you with *clarity* and integrity',
                        'body' => 'To empower entrepreneurs and small businesses through expert consultation, strategic resources, and tailored solutions that foster growth, innovation, and sustainability.',
                        'primary_label' => 'About us',
                        'primary_url' => '/about',
                        'secondary_label' => 'Contact us',
                        'secondary_url' => '/contact-us',
                        'background_id' => MediaSeeder::idFor('about-team'),
                    ]),
                ],
            ],
            [
                'slug' => 'about',
                'title' => 'About Us',
                'seo_title' => 'About Us',
                'seo_description' => 'A business consulting team dedicated to helping entrepreneurs, startups, and small business owners turn ideas into sustainable success.',
                'blocks' => [
                    $this->block('hero', [
                        'variant' => 'split',
                        'eyebrow' => 'About Csinc 91',
                        'heading' => 'Committed to guiding you with *clarity* and integrity',
                        'body' => 'At our core, we are a business consulting team dedicated to helping entrepreneurs, startups, and small business owners turn ideas into sustainable success. We provide practical guidance, strategic insight, and hands-on support tailored to your unique goals.',
                        'primary_label' => 'Contact Us',
                        'primary_url' => '/contact-us',
                        'image_id' => MediaSeeder::idFor('about-team'),
                    ]),
                    $this->block('values', [
                        'eyebrow' => 'Who we are',
                        'heading' => 'What *drives* our mission',
                        'items' => [
                            ['label' => 'Our Mission', 'body' => 'To empower entrepreneurs and small businesses through expert consultation, strategic resources, and tailored solutions that foster growth, innovation, and sustainability.'],
                            ['label' => 'Our Vision', 'body' => 'To be a leading force in transforming ideas into successful businesses — creating accessible, results-driven pathways for entrepreneurs around the world.'],
                            ['label' => 'Our Values', 'body' => 'We build relationships based on honesty, transparency, and accountability — striving for the highest standards in every project, every time.'],
                        ],
                    ]),
                    $this->block('faq', [
                        'eyebrow' => 'FAQ',
                        'heading' => 'Frequently asked *questions*',
                        'items' => [
                            ['question' => 'What type of businesses do you work with?', 'answer' => '<p>We work with a wide range of businesses — from startups and small businesses to established companies — offering customized consultation and support tailored to each client’s needs.</p>'],
                            ['question' => 'Do I need an appointment for document preparation or notary services?', 'answer' => '<p>Yes, we recommend scheduling an appointment to ensure availability and timely service. Walk-ins may be accommodated depending on availability.</p>'],
                            ['question' => 'Can you help me start a new business?', 'answer' => '<p>Absolutely. Our Business Start-Up Consultation service is designed to guide you through every step, from planning and registration to compliance and growth strategies.</p>'],
                            ['question' => 'Are your services available online or only in person?', 'answer' => '<p>We offer both in-person and remote services, depending on your preference and the nature of the service. Many consultations can be conducted over the phone or via video call.</p>'],
                        ],
                    ]),
                    $contactBand,
                ],
            ],
            [
                'slug' => 'services',
                'title' => 'Services',
                'seo_title' => 'Business Services',
                'seo_description' => 'Business formation, business credit development, personal credit consulting and business restructuring — the foundation your business needs to grow and qualify for funding.',
                'blocks' => [
                    $this->block('hero', [
                        'variant' => 'split',
                        'eyebrow' => 'Services',
                        'heading' => 'Building strong businesses from the *foundation* up',
                        'body' => 'From forming your first entity to restructuring an established operation — we provide the financial foundation, credit infrastructure, and strategic framework your business needs to grow, qualify for funding, and sustain long-term success.',
                        'primary_label' => 'Contact Us',
                        'primary_url' => '/contact-us',
                        'image_id' => MediaSeeder::idFor('pillar-business'),
                    ]),
                    $this->block('services', [
                        'eyebrow' => 'Business services',
                        'heading' => 'Our four *core* services',
                        'group' => ServiceGroup::Business->value,
                        'layout' => 'alternating',
                    ]),
                    $this->block('split', [
                        'eyebrow' => 'Website development',
                        'heading' => 'Automation and *marketing* solutions',
                        'body_html' => '<p>Tailored solutions designed to fit your business needs perfectly.</p>'.$automationCopy,
                        'image_id' => MediaSeeder::idFor('website-development'),
                        'image_position' => 'right',
                        'theme' => 'dark',
                        'link_label' => 'Contact us',
                        'link_url' => '/contact-us',
                    ]),
                    $contactBand,
                ],
            ],
            [
                'slug' => 'healthcare',
                'title' => 'Healthcare',
                'seo_title' => 'Healthcare & Housing Consulting',
                'seo_description' => 'Independent consulting for care facility operators, housing entrepreneurs, and healthcare business owners — from startup to sustained operations.',
                'blocks' => [
                    $this->block('hero', [
                        'variant' => 'split',
                        'eyebrow' => 'Healthcare & housing',
                        'heading' => 'Supporting the operators who care for the *most vulnerable*',
                        'body' => 'From startup to sustained operations — independent consulting for care facility operators, housing entrepreneurs, and healthcare business owners at every stage.',
                        'primary_label' => 'Contact Us',
                        'primary_url' => '/contact-us',
                        'secondary_label' => 'Healthcare guides',
                        'secondary_url' => '/product-category/healthcare',
                        'image_id' => MediaSeeder::idFor('residential-care-home'),
                    ]),
                    $this->block('services', [
                        'eyebrow' => 'Healthcare & housing',
                        'heading' => 'What we help you *with*',
                        'group' => ServiceGroup::Healthcare->value,
                        'layout' => 'grid',
                    ]),
                    $this->block('books', [
                        'eyebrow' => 'Healthcare products',
                        'heading' => 'Browse healthcare *startup guides*',
                        'intro' => '{{category.healthcare.count}} done-for-you PDF blueprints — from mobile lab to assisted living & hospice.',
                        'source' => 'category',
                        'category' => 'healthcare',
                        'limit' => 8,
                        'link_label' => 'View Products',
                        'link_url' => '/product-category/healthcare',
                    ]),
                    $this->block('cta_band', [
                        'eyebrow' => 'Contact us',
                        'heading' => 'Get in touch',
                        'body' => 'Have questions or need assistance? Get in touch with us today!',
                        'primary_label' => 'Contact Us',
                        'primary_url' => '/contact-us',
                        'secondary_label' => 'Schedule a consultation',
                        'secondary_url' => 'mailto:Info@CSinc91.com?subject=Consultation%20Request',
                        'background_id' => MediaSeeder::idFor('healthcare-care'),
                    ]),
                ],
            ],
            [
                'slug' => 'products',
                'title' => 'Products',
                'eyebrow' => 'Products & Resources',
                'summary' => 'Step-by-step resources for entrepreneurs, care facility operators, and business owners at every level. All products are instant PDF downloads and include practitioner support.',
                'seo_title' => 'Products — Startup Guides, Workbooks & Templates',
                'seo_description' => 'Step-by-step startup guides, workbooks and templates for entrepreneurs, care facility operators and business owners. Instant PDF downloads with practitioner support.',
                'blocks' => [
                    $this->block('hero', [
                        'variant' => 'text',
                        'eyebrow' => 'Products & Resources',
                        'heading' => 'Startup guides, workbooks & templates designed for *real-world* success',
                        'body' => 'Step-by-step resources for entrepreneurs, care facility operators, and business owners at every level. All products are instant PDF downloads and include practitioner support.',
                    ]),
                ],
            ],
            [
                'slug' => 'contact-us',
                'title' => 'Contact Us',
                'seo_title' => 'Contact Us',
                'seo_description' => 'Whether you need more information, want to book a consultation, or simply need guidance — get in touch with CSinc91 in Atlanta, GA.',
                'blocks' => [
                    $this->block('hero', [
                        'variant' => 'text',
                        'eyebrow' => 'Contact Us',
                        'heading' => 'We’re here to *help*',
                        'body' => 'Whether you need more information, want to book a consultation, or simply need guidance, don’t hesitate to reach out.',
                    ]),
                    $this->block('contact', [
                        'heading' => 'Send us a message',
                        'intro' => 'Tell us about your business and what you would like to achieve. Fields marked * are required.',
                    ]),
                ],
            ],
            [
                'slug' => 'terms-conditions-digital-products',
                'title' => 'Terms & Conditions – Digital Products',
                'seo_title' => 'Terms & Conditions – Digital Products',
                'seo_description' => 'Digital products, refunds and use terms for CSinc91 digital products.',
                'blocks' => [
                    $this->block('hero', ['variant' => 'text', 'eyebrow' => 'Legal', 'heading' => 'Digital Products, Refunds & Use *Terms*']),
                    $this->block('rich_text', ['body_html' => $this->termsHtml()]),
                ],
            ],
            [
                'slug' => 'refund-policy',
                'title' => 'Digital Products – Refund Policy',
                'seo_title' => 'Digital Products – Refund Policy',
                'seo_description' => 'Refund policy for CSinc91 digital, educational and informational products.',
                'blocks' => [
                    $this->block('hero', ['variant' => 'text', 'eyebrow' => 'Legal', 'heading' => 'Digital Products – Refund *Policy*']),
                    $this->block('rich_text', ['body_html' => $this->refundHtml()]),
                ],
            ],
        ];

        foreach ($pages as $page) {
            Page::query()->updateOrCreate(['slug' => $page['slug']], [
                'title' => $page['title'],
                'eyebrow' => $page['eyebrow'] ?? null,
                'summary' => $page['summary'] ?? $page['seo_description'],
                'blocks' => $page['blocks'],
                'status' => PageStatus::Published,
                'is_system' => true,
                'seo_title' => $page['seo_title'],
                'seo_description' => $page['seo_description'],
            ]);
        }
    }

    private function navigation(): void
    {
        NavigationItem::query()->delete();

        $header = [
            ['label' => 'Services', 'url' => '/services', 'description' => 'The financial foundation, credit infrastructure and strategic framework your business needs.', 'children' => [
                ['label' => 'Business Formation', 'url' => '/services#business-formation', 'description' => 'LLC & corporation setup, EIN registration, operating agreements.'],
                ['label' => 'Business Credit Development', 'url' => '/services#business-credit-development', 'description' => 'Trade lines, vendor accounts and funding readiness.'],
                ['label' => 'Personal Credit Consulting', 'url' => '/services#personal-credit-consulting', 'description' => 'Dispute strategy, score optimization and consumer rights.'],
                ['label' => 'Business Restructure', 'url' => '/services#business-restructure', 'description' => 'Entity restructure, compliance audit and scaling strategy.'],
                ['label' => 'Website Development', 'url' => '/services#website-development', 'description' => 'Websites, automation and marketing solutions.'],
            ]],
            ['label' => 'Healthcare', 'url' => '/healthcare', 'description' => 'Independent consulting for care facility operators and healthcare business owners.', 'children' => [
                ['label' => 'Healthcare & Housing', 'url' => '/healthcare#healthcare-housing', 'description' => 'Assisted living, group homes, sober living, veterans housing and hospice.'],
                ['label' => 'Program design & client acquisition', 'url' => '/healthcare#program-design-client-acquisition', 'description' => 'Referral outreach, intake templates and census building.'],
                ['label' => 'Healthcare startup guides', 'url' => '/product-category/healthcare', 'description' => 'Done-for-you PDF blueprints for care operators.'],
            ]],
            ['label' => 'Products', 'url' => '/products', 'description' => 'Startup guides, workbooks & templates — instant PDF downloads with practitioner support.', 'children' => [
                ['label' => 'Healthcare & Residential Care', 'url' => '/product-category/healthcare', 'description' => 'Assisted living, group homes, veterans housing, hospice, transport & care training.'],
                ['label' => 'General Business', 'url' => '/product-category/general-business', 'description' => 'Financial, retail, transport, cleaning, trades, security & service businesses.'],
                ['label' => 'All products', 'url' => '/products', 'description' => 'Browse the complete library.'],
                ['label' => 'My downloads', 'url' => '/downloads', 'description' => 'Get fresh links to resources you have purchased.'],
            ]],
            ['label' => 'About Us', 'url' => '/about'],
            ['label' => 'Contact Us', 'url' => '/contact-us'],
        ];

        $footer = [
            ['label' => 'Services', 'url' => '/services'],
            ['label' => 'Healthcare', 'url' => '/healthcare'],
            ['label' => 'Products', 'url' => '/products'],
            ['label' => 'About Us', 'url' => '/about'],
            ['label' => 'Contact Us', 'url' => '/contact-us'],
            ['label' => 'My downloads', 'url' => '/downloads'],
        ];

        $legal = [
            ['label' => 'Terms & Conditions – Digital Products', 'url' => '/terms-conditions-digital-products'],
            ['label' => 'Refund Policy', 'url' => '/refund-policy'],
        ];

        $this->createNavigation(NavigationLocation::Header, $header);
        $this->createNavigation(NavigationLocation::Footer, $footer);
        $this->createNavigation(NavigationLocation::Legal, $legal);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function createNavigation(NavigationLocation $location, array $items, ?int $parentId = null): void
    {
        foreach ($items as $index => $item) {
            $record = NavigationItem::query()->create([
                'location' => $location,
                'parent_id' => $parentId,
                'label' => $item['label'],
                'url' => $item['url'],
                'description' => $item['description'] ?? null,
                'sort_order' => $index + 1,
                'is_visible' => true,
            ]);

            $this->createNavigation($location, $item['children'] ?? [], $record->getKey());
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{type: string, data: array<string, mixed>}
     */
    private function block(string $type, array $data): array
    {
        return ['type' => $type, 'data' => $data];
    }

    private function termsHtml(): string
    {
        return <<<'HTML'
<p>By purchasing or accessing any digital product from this website, you agree to the following Terms &amp; Conditions. Please review them carefully before completing your purchase.</p>
<h2>A. Nature of Products</h2>
<p>All products offered are digital, educational, and informational in nature. These materials may include, but are not limited to:</p>
<ul><li>Digital guides, PDFs, eBooks, templates, toolkits, and checklists</li><li>Educational frameworks, planning tools, and strategy resources</li><li>Downloadable bundles, zip files, and proprietary content</li></ul>
<p>No physical products are shipped.</p>
<h2>B. No Refund Policy – Final Sale</h2>
<p>Due to the immediate access and irrevocable nature of digital content, all sales are final. Once a digital product has been delivered, accessed, or made available for download, no refunds, exchanges, or cancellations will be issued.</p>
<p>This policy applies regardless of:</p>
<ul><li>Customer expectations or interpretations</li><li>Personal, professional, or business outcomes</li><li>Level of implementation or use</li><li>Perceived usefulness or satisfaction</li></ul>
<h2>C. Educational &amp; Consulting-Adjacent Disclaimer</h2>
<p>All content is provided for general educational purposes only. Nothing on this website or within any digital product constitutes:</p>
<ul><li>Legal advice</li><li>Financial advice</li><li>Medical advice</li><li>Tax advice</li><li>Regulatory, licensing, or compliance advice</li></ul>
<p>Users are encouraged to consult qualified professionals appropriate to their jurisdiction before acting on any information provided.</p>
<h2>D. No Guarantees or Results</h2>
<p>We make no representations or guarantees regarding outcomes, earnings, approvals, compliance status, business success, or results of any kind. Results vary based on individual circumstances, effort, jurisdictional requirements, and external factors beyond our control.</p>
<h2>E. Duplicate Purchases</h2>
<p>In the event of an accidental duplicate purchase of the same digital product, customers must notify us within 48 hours of purchase. At our sole discretion, we may issue a refund or store credit. No obligation is implied.</p>
<h2>F. Technical Access Issues</h2>
<p>If you experience difficulty accessing your purchased product due to a technical issue, please contact <a href="mailto:support@Csinc91.com">support@Csinc91.com</a>. We will make reasonable efforts to restore access or provide an alternative download. Technical issues do not constitute grounds for a refund.</p>
<h2>G. Chargebacks &amp; Payment Disputes</h2>
<p>Unauthorized chargebacks or disputes may result in:</p>
<ul><li>Immediate revocation of access to all digital products</li><li>Suspension or permanent restriction from future purchases</li></ul>
<p>We strongly encourage contacting us directly to resolve concerns before initiating a dispute.</p>
<h2>H. Intellectual Property &amp; Use Restrictions</h2>
<p>All digital products are protected intellectual property. Purchases grant a single-user, non-transferable license for personal or internal business use only.</p>
<p>You may not:</p>
<ul><li>Share, resell, sublicense, distribute, or reproduce the materials</li><li>Upload content to public or private platforms</li><li>Claim the content as your own</li></ul>
<p>Violation may result in legal action.</p>
<h2>I. Acceptance of Terms</h2>
<p>By completing a purchase, you acknowledge that you have read, understood, and agreed to these Terms &amp; Conditions, including the No Refund Policy, in full.</p>
HTML;
    }

    private function refundHtml(): string
    {
        return <<<'HTML'
<p>All products sold on this website are digital, educational, and informational in nature and are delivered electronically.</p>
<h2>No Refunds – Final Sale</h2>
<p>Due to the immediate access and irrevocable delivery of digital content, <strong>all sales are final</strong>. No refunds, exchanges, or cancellations will be issued once access is granted.</p>
<p>This policy applies without exception, including but not limited to:</p>
<ul><li>Change of mind</li><li>Failure to read product descriptions</li><li>Incompatibility with personal, professional, or business needs</li><li>Lack of results or perceived outcomes</li></ul>
<h2>Educational &amp; Consulting-Adjacent Disclaimer</h2>
<p>All content is provided for <strong>general educational purposes only</strong>. Nothing contained in any digital product constitutes legal, financial, medical, tax, licensing, or regulatory advice. Users are solely responsible for how information is interpreted and applied. Professional consultation is strongly recommended prior to implementation.</p>
<h2>No Guarantees</h2>
<p>We make no guarantees, representations, or warranties regarding results, outcomes, approvals, compliance, income, or success of any kind. Results vary based on individual circumstances, jurisdictional requirements, effort, and external factors beyond our control.</p>
<h2>Duplicate Purchases</h2>
<p>If an identical product is purchased more than once in error, contact support within <strong>48 hours</strong>. Any refund or credit is issued <strong>solely at our discretion</strong>.</p>
<h2>Technical Issues</h2>
<p>Technical access issues do not qualify for refunds. We will make reasonable efforts to restore access or reissue files upon request.</p>
<h2>Chargebacks &amp; Disputes</h2>
<p>Unauthorized chargebacks may result in:</p>
<ul><li>Immediate termination of access</li><li>Permanent restriction from future purchases</li></ul>
<h2>Intellectual Property</h2>
<p>All materials are protected intellectual property and licensed for <strong>single-user use only</strong>. Unauthorized distribution or reproduction may result in legal action.</p>
<h2>Acceptance</h2>
<p>By completing a purchase, you acknowledge and agree to this policy and all associated Terms &amp; Conditions.</p>
<p>Support: <a href="mailto:support@Csinc91.com">support@Csinc91.com</a><br>Website: <a href="https://csinc91.com">www.Csinc91.com</a></p>
HTML;
    }
}
