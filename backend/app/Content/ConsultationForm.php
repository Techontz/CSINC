<?php

namespace App\Content;

/**
 * Option lists for the Business Consulting Inquiry form, carried over from the
 * original CSinc91 intake form so submissions stay comparable over time.
 */
class ConsultationForm
{
    public const INDUSTRIES = ['Service-Based', 'Healthcare-related', 'Finance Support', 'Education', 'Retail'];

    public const GROWTH_STAGES = ['Startup', 'Growth Phase', 'Mature', 'Scaling'];

    public const CHALLENGES = ['Strategy Development', 'Market Positioning', 'Competitive Analysis'];

    public const GOALS = ['Revenue', 'Customer Retention', 'Operational Efficiency', 'Team Development'];

    public const SERVICES_OF_INTEREST = ['Strategic Planning', 'Team Building', 'Process Optimization', 'Financial Advisory'];

    public const PREFERRED_SERVICES = ['Business Formation', 'Business Restructure', 'Business Expansion'];

    /**
     * @return array<string, list<string>>
     */
    public static function options(): array
    {
        return [
            'industries' => self::INDUSTRIES,
            'growth_stages' => self::GROWTH_STAGES,
            'challenges' => self::CHALLENGES,
            'goals' => self::GOALS,
            'services_of_interest' => self::SERVICES_OF_INTEREST,
            'preferred_services' => self::PREFERRED_SERVICES,
        ];
    }

    /**
     * Human labels for the structured details stored with each submission.
     *
     * @return array<string, string>
     */
    public static function detailLabels(): array
    {
        return [
            'address' => 'Address',
            'industry' => 'Industry',
            'growth_stage' => 'Growth stage',
            'challenges' => 'Challenges',
            'primary_goal' => 'Primary goal',
            'topic' => 'Topic of interest',
            'preferred_start_date' => 'Preferred start date',
            'service_of_interest' => 'Service of interest',
            'preferred_service' => 'Preferred service',
            'product' => 'Related product',
            'signature' => 'Signed as',
        ];
    }
}
