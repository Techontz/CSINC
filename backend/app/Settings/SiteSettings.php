<?php

namespace App\Settings;

use App\Http\Resources\V1\MediaResource;
use App\Models\Media;
use App\Models\SiteSetting;

/**
 * Typed access to CMS-managed site settings, with sensible defaults.
 */
class SiteSettings
{
    /** @var array<string, mixed> */
    public const DEFAULTS = [
        'site.name' => 'CSinc91',
        'site.legal_name' => 'CSinc91',
        'site.tagline' => 'Web Development, Business Automation and Digital Marketing',
        'site.description' => 'Strategic consulting, startup blueprints, and done-for-you business infrastructure for entrepreneurs, care-facility operators, and small business owners at every stage.',
        'site.logo_id' => null,
        'site.logo_inverse_id' => null,
        'contact.email' => 'Info@CSinc91.com',
        'contact.support_email' => 'support@Csinc91.com',
        'contact.phone' => null,
        'contact.address_line_1' => '8735 Dunwoody Place, Ste N',
        'contact.address_line_2' => null,
        'contact.city' => 'Atlanta',
        'contact.region' => 'GA',
        'contact.postal_code' => '30350',
        'contact.country' => 'USA',
        'contact.hours' => null,
        'contact.consultation_url' => 'mailto:Info@CSinc91.com?subject=Consultation%20Request',
        'contact.notification_email' => 'Info@CSinc91.com',
        'social.links' => [],
        'seo.title_template' => '%s — CSinc91',
        'seo.default_title' => 'CSinc91 — Web Development, Business Automation and Digital Marketing',
        'seo.default_description' => 'Strategic consulting, startup blueprints, and done-for-you business infrastructure for entrepreneurs, care-facility operators, and small business owners.',
        'seo.og_image_id' => null,
        'footer.statement' => 'Transforming ideas into scalable business success.',
        'footer.newsletter_text' => null,
        'commerce.enabled' => true,
        'commerce.currency' => 'USD',
        'commerce.download_expiry_hours' => 72,
        'commerce.checkout_note' => 'All products are digital, educational resources delivered as instant PDF downloads. All sales are final.',
    ];

    public function get(string $key): mixed
    {
        return SiteSetting::get($key, self::DEFAULTS[$key] ?? null);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_replace(self::DEFAULTS, array_intersect_key(SiteSetting::allValues(), self::DEFAULTS));
    }

    /**
     * Public, render-ready payload. Internal keys (notification email) are excluded.
     *
     * @return array<string, mixed>
     */
    public function publicPayload(): array
    {
        $values = $this->all();
        $media = Media::query()
            ->whereIn('id', array_filter([$values['site.logo_id'], $values['site.logo_inverse_id'], $values['seo.og_image_id']]))
            ->get()
            ->keyBy('id');
        $image = fn ($id): ?array => $id && $media->has($id) ? MediaResource::make($media->get($id))->resolve() : null;

        return [
            'name' => $values['site.name'],
            'legal_name' => $values['site.legal_name'],
            'tagline' => $values['site.tagline'],
            'description' => $values['site.description'],
            'logo' => $image($values['site.logo_id']),
            'logo_inverse' => $image($values['site.logo_inverse_id']),
            'contact' => [
                'email' => $values['contact.email'],
                'support_email' => $values['contact.support_email'],
                'phone' => $values['contact.phone'],
                'address' => [
                    'line_1' => $values['contact.address_line_1'],
                    'line_2' => $values['contact.address_line_2'],
                    'city' => $values['contact.city'],
                    'region' => $values['contact.region'],
                    'postal_code' => $values['contact.postal_code'],
                    'country' => $values['contact.country'],
                ],
                'hours' => $values['contact.hours'],
                'consultation_url' => $values['contact.consultation_url'],
            ],
            'social' => array_values(array_filter(
                (array) $values['social.links'],
                fn ($link): bool => is_array($link) && filled($link['url'] ?? null),
            )),
            'seo' => [
                'title_template' => $values['seo.title_template'],
                'default_title' => $values['seo.default_title'],
                'default_description' => $values['seo.default_description'],
                'image' => $image($values['seo.og_image_id']),
            ],
            'footer' => [
                'statement' => $values['footer.statement'],
            ],
            'commerce' => [
                'enabled' => (bool) $values['commerce.enabled'],
                'currency' => $values['commerce.currency'],
                'checkout_note' => $values['commerce.checkout_note'],
            ],
        ];
    }
}
