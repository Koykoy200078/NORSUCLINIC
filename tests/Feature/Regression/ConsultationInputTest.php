<?php

namespace Tests\Feature\Regression;

use App\Support\ConsultationInput;
use PHPUnit\Framework\TestCase;

class ConsultationInputTest extends TestCase
{
    /** @dataProvider inputs */
    public function test_nullable_inputs_keep_the_existing_conversion(mixed $value, ?int $integer, ?string $string): void
    {
        $this->assertSame($integer, ConsultationInput::nullableInt($value));
        $this->assertSame($string, ConsultationInput::nullableString($value));
    }

    public static function inputs(): array
    {
        return [
            'null' => [null, null, null],
            'empty' => ['', null, null],
            'spaces' => ['  ', null, null],
            'zero string' => [' 0 ', 0, '0'],
            'zero number' => [0, 0, '0'],
            'fraction' => ['2.9', 2, '2.9'],
            'exponent' => ['2e1', 20, '2e1'],
            'text' => ['invalid', null, 'invalid'],
            'false' => [false, null, null],
            'true' => [true, null, '1'],
            'unicode' => [' café ', null, 'café'],
        ];
    }
}
