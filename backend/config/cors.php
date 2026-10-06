<?php

/*
| The public website calls this API from its own server (Next.js route handlers),
| so browsers only need cross-origin access from the website's own origins.
| Admin API clients authenticate with bearer tokens, not cookies, so
| credentials are not shared cross-origin.
*/

$origins = env('CORS_ALLOWED_ORIGINS') ?: env('FRONTEND_URL', 'http://localhost:3091');

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_filter(array_map(
        fn (string $origin): string => rtrim(trim($origin), '/'),
        explode(',', (string) $origins),
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Authorization', 'Content-Type', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
