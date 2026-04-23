<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LabTest;

class LabTestSeeder extends Seeder
{
    /**
     * Seed the lab_tests table with common laboratory and medical tests.
     */
    public function run(): void
    {
        $tests = [
            // ----------------------------------------------------------------
            // Laboratory — Clinical Chemistry
            // ----------------------------------------------------------------
            ['name' => 'Complete Blood Count (CBC)',         'category' => 'Laboratory',     'unit' => null,        'normal_range' => null],
            ['name' => 'Fasting Blood Sugar (FBS)',          'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => '70–99 mg/dL'],
            ['name' => 'Random Blood Sugar (RBS)',           'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => '<140 mg/dL'],
            ['name' => 'HbA1c (Glycated Hemoglobin)',        'category' => 'Laboratory',     'unit' => '%',         'normal_range' => '<5.7%'],
            ['name' => 'Lipid Profile',                      'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => null],
            ['name' => 'Total Cholesterol',                  'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => '<200 mg/dL'],
            ['name' => 'Triglycerides',                      'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => '<150 mg/dL'],
            ['name' => 'HDL Cholesterol',                    'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => '>40 mg/dL'],
            ['name' => 'LDL Cholesterol',                    'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => '<100 mg/dL'],
            ['name' => 'Creatinine',                         'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => '0.6–1.2 mg/dL'],
            ['name' => 'Blood Urea Nitrogen (BUN)',          'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => '7–20 mg/dL'],
            ['name' => 'Uric Acid',                          'category' => 'Laboratory',     'unit' => 'mg/dL',     'normal_range' => '3.5–7.2 mg/dL'],
            ['name' => 'SGPT / ALT (Liver Function)',        'category' => 'Laboratory',     'unit' => 'U/L',       'normal_range' => '7–56 U/L'],
            ['name' => 'SGOT / AST (Liver Function)',        'category' => 'Laboratory',     'unit' => 'U/L',       'normal_range' => '10–40 U/L'],
            ['name' => 'Urinalysis',                         'category' => 'Laboratory',     'unit' => null,        'normal_range' => null],
            ['name' => 'Stool Exam',                         'category' => 'Laboratory',     'unit' => null,        'normal_range' => null],
            ['name' => 'Sodium (Na)',                        'category' => 'Laboratory',     'unit' => 'mEq/L',     'normal_range' => '135–145 mEq/L'],
            ['name' => 'Potassium (K)',                      'category' => 'Laboratory',     'unit' => 'mEq/L',     'normal_range' => '3.5–5.0 mEq/L'],

            // ----------------------------------------------------------------
            // Hematology
            // ----------------------------------------------------------------
            ['name' => 'Blood Typing (ABO & Rh)',            'category' => 'Hematology',     'unit' => null,        'normal_range' => null],
            ['name' => 'Prothrombin Time (PT)',               'category' => 'Hematology',     'unit' => 'seconds',   'normal_range' => '11–13.5 seconds'],
            ['name' => 'Partial Thromboplastin Time (PTT)',   'category' => 'Hematology',     'unit' => 'seconds',   'normal_range' => '25–35 seconds'],
            ['name' => 'Erythrocyte Sedimentation Rate (ESR)','category' => 'Hematology',    'unit' => 'mm/hr',     'normal_range' => null],
            ['name' => 'Platelet Count',                     'category' => 'Hematology',     'unit' => '×10³/µL',   'normal_range' => '150–400 ×10³/µL'],

            // ----------------------------------------------------------------
            // Microbiology / Serology
            // ----------------------------------------------------------------
            ['name' => 'Culture & Sensitivity (C&S)',        'category' => 'Microbiology',   'unit' => null,        'normal_range' => null],
            ['name' => 'AFB Smear (TB Test)',                'category' => 'Microbiology',   'unit' => null,        'normal_range' => null],
            ['name' => 'Hepatitis B Surface Antigen (HBsAg)','category' => 'Microbiology',   'unit' => null,        'normal_range' => 'Non-reactive'],
            ['name' => 'HIV Screening (Anti-HIV 1&2)',       'category' => 'Microbiology',   'unit' => null,        'normal_range' => 'Non-reactive'],
            ['name' => 'Pregnancy Test (urine hCG)',         'category' => 'Microbiology',   'unit' => null,        'normal_range' => null],
            ['name' => 'Drug Test (DOLE-standard)',          'category' => 'Microbiology',   'unit' => null,        'normal_range' => null],

            // ----------------------------------------------------------------
            // Radiology
            // ----------------------------------------------------------------
            ['name' => 'Chest X-Ray (PA view)',              'category' => 'Radiology',      'unit' => null,        'normal_range' => null],
            ['name' => 'Chest X-Ray (AP view)',              'category' => 'Radiology',      'unit' => null,        'normal_range' => null],
            ['name' => 'Abdominal X-Ray',                    'category' => 'Radiology',      'unit' => null,        'normal_range' => null],
            ['name' => 'Skull X-Ray',                        'category' => 'Radiology',      'unit' => null,        'normal_range' => null],
            ['name' => 'Ultrasound — Whole Abdomen',         'category' => 'Radiology',      'unit' => null,        'normal_range' => null],

            // ----------------------------------------------------------------
            // Cardiac
            // ----------------------------------------------------------------
            ['name' => '12-Lead ECG',                        'category' => 'Cardiac',        'unit' => null,        'normal_range' => null],
            ['name' => '2D Echocardiography',                'category' => 'Cardiac',        'unit' => null,        'normal_range' => null],

            // ----------------------------------------------------------------
            // Other
            // ----------------------------------------------------------------
            ['name' => 'Sputum Gram Stain',                  'category' => 'Other',          'unit' => null,        'normal_range' => null],
            ['name' => 'Pap Smear',                          'category' => 'Other',          'unit' => null,        'normal_range' => null],
        ];

        foreach ($tests as $test) {
            LabTest::firstOrCreate(
                ['name' => $test['name'], 'category' => $test['category']],
                array_merge($test, ['is_active' => true])
            );
        }
    }
}
