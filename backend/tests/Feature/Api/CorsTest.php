<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class CorsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['cors.allowed_origins' => ['https://csinc91.com', 'https://www.csinc91.com']]);
    }

    public function test_the_website_origin_may_call_the_api(): void
    {
        $this->call('OPTIONS', '/api/v1/contact', server: [
            'HTTP_ORIGIN' => 'https://www.csinc91.com',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ])
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://www.csinc91.com')
            ->assertHeaderMissing('Access-Control-Allow-Credentials');
    }

    public function test_other_origins_are_not_allowed(): void
    {
        $this->call('OPTIONS', '/api/v1/contact', server: [
            'HTTP_ORIGIN' => 'https://evil.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ])->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
