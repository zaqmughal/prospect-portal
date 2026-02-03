<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\LeadSource;
use App\Services\Discovery\DiscoveryRunResult;

interface DiscoveryConnector
{
    public function run(LeadSource $source): DiscoveryRunResult;
}
