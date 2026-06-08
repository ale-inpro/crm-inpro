<?php
declare(strict_types=1);

return [
    'resend_api_key' => env('RESEND_API_KEY', 're_f659DemA_7fz5u9x4Af7ejAKMr7GbGGRH'),
    'from'           => env('RESEND_FROM_EMAIL', 'CRM INPRO <onboarding@resend.dev>'),
    'intercept_to'   => env('RESEND_INTERCEPT_TO', ''),
];
