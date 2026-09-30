<?php

namespace Tests\Unit;

use App\Rules\PhilippinePhoneNumber;
use App\Support\PhilippinePhone;
use PHPUnit\Framework\TestCase;

/**
 * The +63 phone convention (Phase E of the 2026-09 re-audit remediation). The browser side
 * (resources/assets/js/custom/ph-phone-input.js) mirrors PhilippinePhone::national() exactly.
 */
class PhilippinePhoneTest extends TestCase
{
    /**
     * @return array<string, array{0: mixed, 1: ?string}>
     */
    public static function spellings(): array
    {
        return [
            'international with spaces' => ['+63 917 123 4567', '9171234567'],
            'international with dashes' => ['63917-123-4567', '9171234567'],
            'local with trunk zero' => ['09171234567', '9171234567'],
            'national only' => ['9171234567', '9171234567'],
            'integer input' => [9171234567, '9171234567'],
            '0063 prefix' => ['0063 917 123 4567', '9171234567'],
            'legacy +63 joined to 09...' => ['+6309171234567', '9171234567'],
            'brackets and dashes' => ['(0917) 123-4567', '9171234567'],
            'dots' => ['917.123.4567', '9171234567'],
            'provincial landline (Dumaguete 35)' => ['(035) 422-1234', '354221234'],
            'landline international' => ['+63 35 422 1234', '354221234'],
            'Metro Manila landline' => ['02 8123 4567', '281234567'],
            'Metro Manila international' => ['+63 2 8123 4567', '281234567'],
            'foreign number' => ['+1 917 123 4567', null],
            'too short mobile' => ['09171234', null],
            'too long mobile' => ['091712345678', null],
            'text' => ['N/A', null],
            'extension text' => ['+63 9171234567 ext 5', null],
            'empty' => ['', null],
            'null' => [null, null],
            'only trunk zeros' => ['0000000000', null],
            'seven digit local number without area code' => ['5225050', null],
            'placeholder that used to be seeded' => ['1234567890', null],
        ];
    }

    /**
     * @dataProvider spellings
     */
    public function test_any_common_spelling_reduces_to_the_national_number(mixed $input, ?string $expected): void
    {
        $this->assertSame($expected, PhilippinePhone::national($input));
        $this->assertSame($expected !== null, PhilippinePhone::isValid($input));
    }

    public function test_display_and_storage_forms(): void
    {
        $this->assertSame('+639171234567', PhilippinePhone::e164('0917 123 4567'));
        $this->assertSame('+63 917 123 4567', PhilippinePhone::format('09171234567'));
        $this->assertSame('+63 35 4221234', PhilippinePhone::format('(035) 422-1234'));
        $this->assertSame('+63 2 81234567', PhilippinePhone::format('02-8123-4567'));
        $this->assertNull(PhilippinePhone::e164('N/A'));
        $this->assertNull(PhilippinePhone::format('+1 415 555 2671'));
    }

    public function test_the_validation_rule_accepts_blank_and_philippine_numbers_only(): void
    {
        $rule = new PhilippinePhoneNumber();

        $this->assertTrue($rule->passes('contact', null));
        $this->assertTrue($rule->passes('contact', ''));
        $this->assertTrue($rule->passes('contact', '+63 917 123 4567'));
        $this->assertTrue($rule->passes('contact', '9171234567'));
        $this->assertFalse($rule->passes('contact', '+1 415 555 2671'));
        $this->assertFalse($rule->passes('contact', 'abc'));
    }
}
