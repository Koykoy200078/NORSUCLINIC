<?php

namespace Database\Seeders;

use App\Models\Diagnose;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiagnoseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $diagnose = [
            [
                'diagnoses' => 'None',
            ],
            [
                'diagnoses' => 'Tuberculosis (TB)',
            ],
            [
                'diagnoses' => 'Dengue Fever',
            ],
            [
                'diagnoses' => 'Human Immunodeficiency Virus (HIV)/AIDS',
            ],
            [
                'diagnoses' => 'Malaria',
            ],
            [
                'diagnoses' => 'Leptospirosis',
            ],
            [
                'diagnoses' => 'Hepatitis',
            ],
            [
                'diagnoses' => 'Diabetes Mellitus',
            ],
            [
                'diagnoses' => 'Cardiovascular Diseases',
            ],
            [
                'diagnoses' => 'Chronic Obstructive Pulmonary Disease (COPD)',
            ],
            [
                'diagnoses' => 'Cancer',
            ],
            [
                'diagnoses' => 'Pre-eclampsia and Eclampsia',
            ],
            [
                'diagnoses' => 'Gestational Diabetes',
            ],
            [
                'diagnoses' => 'Malnutrition',
            ],
            [
                'diagnoses' => 'Neonatal Jaundice',
            ],
            [
                'diagnoses' => 'Low Birth Weight',
            ],
            [
                'diagnoses' => 'Depression',
            ],
            [
                'diagnoses' => 'Anxiety Disorders',
            ],
            [
                'diagnoses' => 'Bipolar Disorder',
            ],
            [
                'diagnoses' => 'Schizophrenia',
            ],
            [
                'diagnoses' => 'Substance Use Disorders',
            ],
            [
                'diagnoses' => 'Pneumonia',
            ],
            [
                'diagnoses' => 'Asthma',
            ],
            [
                'diagnoses' => 'Bronchitis',
            ],
            [
                'diagnoses' => 'Influenza',
            ],
            [
                'diagnoses' => 'Scabies',
            ],
            [
                'diagnoses' => 'Leprosy',
            ],
            [
                'diagnoses' => 'Fungal Infections',
            ],
            [
                'diagnoses' => 'Filariasis',
            ],
            [
                'diagnoses' => 'Gonorrhea',
            ],
            [
                'diagnoses' => 'Syphilis',
            ],
            [
                'diagnoses' => 'Chlamydia',
            ],
            [
                'diagnoses' => 'Iron-Deficiency Anemia',
            ],
            [
                'diagnoses' => 'Vitamin A Deficiency',
            ],
            [
                'diagnoses' => 'Iodine Deficiency Disorders',
            ],
            [
                'diagnoses' => 'Fractures',
            ],
            [
                'diagnoses' => 'Burns',
            ],
            [
                'diagnoses' => 'Poisoning',
            ],
            [
                'diagnoses' => 'Road Traffic Injuries',
            ],
            [
                'diagnoses' => 'Epilepsy',
            ],
            [
                'diagnoses' => 'Cerebrovascular Disease',
            ],
            [
                'diagnoses' => 'Alzheimer’s Disease',
            ],
        ];

        Diagnose::insert($diagnose);
    }
}
