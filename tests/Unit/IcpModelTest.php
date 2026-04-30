<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Icp;
use Tests\TestCase;

class IcpModelTest extends TestCase
{
    public function test_fillable_does_not_include_size_bands(): void
    {
        $icp = new Icp;
        $this->assertNotContains('size_bands', $icp->getFillable());
    }
}
