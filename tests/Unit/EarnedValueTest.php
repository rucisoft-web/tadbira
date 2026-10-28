<?php

namespace Tests\Unit;

use App\Services\Performance\EarnedValue;
use PHPUnit\Framework\TestCase;

class EarnedValueTest extends TestCase
{
    public function test_indices_and_forecast(): void
    {
        // BAC 100 jt, rencana 50%, fisik 40%, biaya 50 jt.
        $e = EarnedValue::compute(100_000_000, 50, 40, 50_000_000);

        $this->assertEquals(50_000_000, $e['pv']);
        $this->assertEquals(40_000_000, $e['ev']);
        $this->assertEquals(-10_000_000, $e['sv']);
        $this->assertEquals(-10_000_000, $e['cv']);
        $this->assertSame(0.8, $e['spi']);
        $this->assertSame(0.8, $e['cpi']);
        $this->assertEquals(125_000_000, $e['eac']);
        $this->assertEquals(75_000_000, $e['etc']);
        $this->assertEquals(-25_000_000, $e['vac']);
        $this->assertSame(1.2, $e['tcpi']);
    }

    public function test_no_cost_yet(): void
    {
        $e = EarnedValue::compute(10_000_000, 0, 0, 0);

        $this->assertNull($e['spi']);
        $this->assertNull($e['cpi']);
        $this->assertEquals(10_000_000, $e['eac']);
    }
}
