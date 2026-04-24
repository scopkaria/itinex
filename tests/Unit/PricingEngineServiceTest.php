<?php

namespace Tests\Unit;

use App\Services\Pricing\PricingEngineService;
use App\Services\Pricing\PricingInput;
use PHPUnit\Framework\TestCase;

class PricingEngineServiceTest extends TestCase
{
    public function test_it_calculates_dynamic_price_with_markup_and_vat(): void
    {
        $service = new PricingEngineService();

        $input = new PricingInput(
            baseRate: 100,
            seasonalPercent: 10,
            mealPlanAdjustment: 15,
            roomTypeAdjustment: 5,
            childDiscountPercent: 10,
            singleSupplement: 20,
            tripleReduction: 5,
            transportTotal: 30,
            activitiesTotal: 10,
            flightsTotal: 40,
            discountPercent: 5,
            markupPercent: 20,
            vatPercent: 18,
            vatType: 'exclusive'
        );

        $result = $service->compute($input);

        $this->assertSame(100.0, $result->baseRate);
        $this->assertSame(10.0, $result->seasonalAdjustment);
        $this->assertSame(20.0, $result->configurationAdjustments);
        $this->assertSame(-10.0, $result->childAdjustments);
        $this->assertSame(15.0, $result->supplements);
        $this->assertSame(80.0, $result->moduleTotals);
        $this->assertSame(215.0, $result->subtotal);
        $this->assertSame(10.75, $result->discountAmount);
        $this->assertSame(40.85, $result->markupAmount);
        $this->assertSame(44.12, $result->vatAmount);
        $this->assertSame(289.22, $result->finalTotal);
    }

    public function test_it_calculates_inclusive_vat_without_adding_extra(): void
    {
        $service = new PricingEngineService();

        $input = new PricingInput(
            baseRate: 118,
            vatPercent: 18,
            vatType: 'inclusive'
        );

        $result = $service->compute($input);

        $this->assertSame(118.0, $result->finalTotal);
        $this->assertSame(18.0, $result->vatAmount);
    }
}
