<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

/**
 * The "OTHER SERVICES" rows of the ACCOMPLISHMENT REPORT. A row with an auto_rule is counted by the system from
 * data it already records (vital signs, medicines given, certificates, lab requests); the others are ticked by the
 * nurse on the consultation form. Safe to run again.
 */
class ServiceTypeSeeder extends Seeder
{
    public const CLINICAL = 'clinical_procedure';

    public const PROMOTION = 'health_promotion';

    /**
     * @return array<string, array<int, array{0: string, 1: ?string, 2?: bool}>> category => [name, auto rule, is "Others"]
     */
    public static function list(): array
    {
        return [
            self::CLINICAL => [
                ['Height taking / Weight taking / BMI taking', 'height_weight'],
                ['BP taking only', 'bp_only'],
                ['Vital signs taking (PR, RR, T) only', 'vitals_only'],
                ['Oxygen saturation taking only', 'o2_only'],
                ['Wound treatment/dressing', null],
                ['Optic drops instillation only', null],
                ['Hyperventilation bag breathing only', null],
                ['Nebulization only', null],
                ['Liniment oil application only', null],
                ['Tetanus injection', null],
                ['Warm bag application only', null],
                ['Cold/Ice compress only', null],
                ['Others', null, true],
            ],
            self::PROMOTION => [
                ['Return check-up', null],
                ['Request for laboratory/diagnostic exam / workup request only', 'lab_request'],
                ['Review of laboratory/diagnostic results', null],
                ['Medicine assistance', 'medicine_given'],
                ['Medical certificate issuance', 'medical_certificate'],
                ['Referrals', null],
                ['Maintenance medications prescription', null],
                ['Physical examination/certificate for: Employment', null],
                ['Physical examination/certificate for: Athletic activities', null],
                ['Physical examination/certificate for: ROTC and other special events', null],
                ['COVID-19 concerns/health advice', null],
                ['Others', null, true],
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::list() as $category => $services) {
            $order = 0;

            foreach ($services as $service) {
                [$name, $autoRule] = $service;
                $isOther = $service[2] ?? false;

                ServiceType::firstOrCreate(
                    ['category' => $category, 'name' => $name],
                    ['auto_rule' => $autoRule, 'is_other' => $isOther, 'sort_order' => ++$order, 'is_active' => true]
                );
            }
        }
    }
}
