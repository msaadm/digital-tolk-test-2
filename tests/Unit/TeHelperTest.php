<?php
# test/Unit/TeHelperTest.php

namespace Tests\Unit;

use DTApi\Helpers\TeHelper;
use Tests\TestCase;

class TeHelperTest extends TestCase
{
    /**
     * A willExpireAt test.
     *
     * @return void
     */
    public function testWillExpireAtMoreThan90()
    {
        $created_at = Carbon\Carbon::parse('2024-01-07 12:00:00');
        $due_time = $created_at->clone()->addHours(96);

        $actual = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals(
            "2024-01-09 12:00:00",
            $actual,
            "willExpirateAtMoreThan90 value is not equals to expected"
        );
    }

    public function testWillExpireAtMoreThan72()
    {
        $created_at = Carbon\Carbon::parse('2024-01-07 12:00:00');
        $due_time = $created_at->clone()->addHours(84);

        $actual = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals(
            "2024-01-11 00:00:00",
            $actual,
            "willExpirateAtMoreThan72 value is not equals to expected"
        );
    }

    public function testWillExpireAtMoreThan24()
    {
        $created_at = Carbon\Carbon::parse('2024-01-07 12:00:00');
        $due_time = $created_at->clone()->addHours(45);

        $actual = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals(
            "2024-01-08 04:00:00",
            $actual,
            "willExpirateAtMoreThan24 value is not equals to expected"
        );
    }

    public function testWillExpireAtLessThan24()
    {
        $created_at = Carbon\Carbon::parse('2024-01-07 12:00:00');
        $due_time = $created_at->clone()->addHours(23);

        $actual = TeHelper::willExpireAt($due_time, $created_at);

        $this->assertEquals(
            "2024-01-07 13:30:00",
            $actual,
            "willExpirateAtLessThan24 value is not equals to expected"
        );
    }
}
