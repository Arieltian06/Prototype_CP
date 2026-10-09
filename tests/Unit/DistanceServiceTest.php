<?php

namespace Tests\Unit;

use App\Services\DistanceService;
use DomainException;
use PHPUnit\Framework\TestCase;

class DistanceServiceTest extends TestCase
{
    public function test_same_coordinates_have_zero_distance(): void
    {
        $this->assertSame(0.0, (new DistanceService)->calculateDistance(-3.8, 102.2, -3.8, 102.2));
    }

    public function test_one_degree_at_equator_is_about_111_195_km(): void
    {
        $this->assertEqualsWithDelta(111.195, (new DistanceService)->calculateDistance(0, 0, 0, 1), 0.001);
    }

    public function test_distance_is_symmetric_and_handles_date_line(): void
    {
        $service = new DistanceService;
        $forward = $service->calculateDistance(0, 179, 0, -179);
        $this->assertEqualsWithDelta(222.390, $forward, 0.001);
        $this->assertEqualsWithDelta($forward, $service->calculateDistance(0, -179, 0, 179), 1e-9);
    }

    public function test_antipodal_distance_is_finite(): void
    {
        $this->assertEqualsWithDelta(20015.114, (new DistanceService)->calculateDistance(0, 0, 0, 180), 0.001);
    }

    public function test_out_of_range_coordinates_are_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new DistanceService)->calculateDistance(91, 0, 0, 0);
    }

    public function test_non_finite_coordinates_are_rejected(): void
    {
        $this->expectException(DomainException::class);
        (new DistanceService)->calculateDistance(NAN, 0, 0, 0);
    }
}
