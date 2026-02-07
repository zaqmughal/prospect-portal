<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Outreach Configuration
    |--------------------------------------------------------------------------
    |
    | Company description and links used by the outreach writer prompt so
    | generated emails and DMs include Hare & Tortoise positioning and
    | relevant links (website, LinkedIn, case study).
    |
    */

    'company_website' => env('OUTREACH_COMPANY_WEBSITE', 'https://hareandtortoise.agency/'),

    'linkedin_url' => env('OUTREACH_LINKEDIN_URL', 'https://www.linkedin.com/company/hare-tortoise-agency/'),

    'case_study_url' => env('OUTREACH_CASE_STUDY_URL', 'https://hareandtortoise.agency/case-studies/cg-education/'),

    'company_description' => env(
        'OUTREACH_COMPANY_DESCRIPTION',
        'Hare & Tortoise is a boutique web development consultancy combining AI-accelerated delivery with senior engineering oversight. We help organisations ship high-quality digital platforms faster — without sacrificing accessibility (WCAG AA), security, or long-term maintainability. Whether you\'re launching something new or modernising an existing platform, we help you move forward with confidence.'
    ),

];
