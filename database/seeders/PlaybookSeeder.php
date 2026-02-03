<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Playbook;
use Illuminate\Database\Seeder;

class PlaybookSeeder extends Seeder
{
    public function run(): void
    {
        Playbook::updateOrCreate(
            ['name' => 'Accessibility & UX'],
            [
                'angle' => 'accessibility_ux',
                'dm_template' => "Hi {first_name},

I came across {company_name} and noticed some opportunities to enhance your website's accessibility and user experience.

As someone who works with organisations in the {sector} sector, I've helped similar teams improve their digital presence while meeting WCAG standards.

Would you be open to a quick chat about how we could help?

Best,
Zaq",
                'email_template' => "Hi {first_name},

I hope this email finds you well. I'm reaching out because I've been looking at {company_name}'s website and identified some opportunities that could significantly improve your user experience and accessibility compliance.

Specifically, I noticed:
{key_signals}

At Hare and Tortoise, we specialise in helping organisations like yours create inclusive digital experiences. We combine AI-powered insights with human expertise to deliver high-quality results efficiently.

Would you have 15 minutes this week for a quick call to discuss how we might help?

Best regards,
Zaq Mughal
Hare and Tortoise Digital",
                'constraints' => [
                    'min_signals' => 2,
                    'required_signal_types' => ['ux', 'content'],
                ],
                'is_active' => true,
            ]
        );

        Playbook::updateOrCreate(
            ['name' => 'Modernisation'],
            [
                'angle' => 'modernisation',
                'dm_template' => "Hi {first_name},

I noticed {company_name}'s website could benefit from a refresh - particularly around {main_signal}.

We help organisations modernise their web presence without the typical agency overhead. Would you be interested in learning more?

Best,
Zaq",
                'email_template' => "Hi {first_name},

I've been researching organisations in the {sector} space and came across {company_name}. Your website shows great potential, but there are some areas that could use modernisation:

{key_signals}

At Hare and Tortoise, we help organisations transform their digital presence through modern, performant, and accessible web solutions. Our approach combines AI efficiency with human expertise to deliver exceptional results.

I'd love to share some ideas on how we could help {company_name} achieve its digital goals. Would you be available for a brief call this week?

Looking forward to hearing from you,
Zaq Mughal
Hare and Tortoise Digital",
                'constraints' => [
                    'min_signals' => 1,
                    'required_signal_types' => ['tech'],
                ],
                'is_active' => true,
            ]
        );
    }
}
