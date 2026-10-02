<?php

namespace Database\Seeders;

use App\Models\Illness;
use App\Models\IllnessSystem;
use Illuminate\Database\Seeder;

/**
 * The illness list of the university clinic's ACCOMPLISHMENT REPORT, grouped by body system exactly like the
 * report. Every system ends with an "Others" line that takes free text. Safe to run again: rows are matched by
 * name, so edits made later are not overwritten and nothing is duplicated.
 */
class IllnessSeeder extends Seeder
{
    /**
     * system => list of [name, group label (optional)]. A null name group means no sub-heading.
     *
     * @return array<string, array<int, array{0: string, 1: ?string}>>
     */
    public static function list(): array
    {
        return [
            'Respiratory System' => [
                ['Cough/colds', 'Upper Respiratory Infections'],
                ['Acute tonsillitis', 'Upper Respiratory Infections'],
                ['Pharyngitis', 'Upper Respiratory Infections'],
                ['Allergic rhinitis', 'Upper Respiratory Infections'],
                ['Asthma', 'Upper Respiratory Infections'],
                ['Sinusitis', 'Upper Respiratory Infections'],
                ['Laryngitis', 'Upper Respiratory Infections'],
                ['Bronchitis', 'Lower Respiratory Infections'],
                ['Pneumonia', 'Lower Respiratory Infections'],
                ['Pulmonary tuberculosis (PTB)', 'Lower Respiratory Infections'],
            ],
            'Circulatory System' => [
                ['Hyperventilation (shortness of breath / difficulty of breathing)', null],
                ['Hypertension', null],
                ['Hypotension', null],
                ['Anemia', null],
                ['Chest pains/angina pectoris', null],
                ['Heart pathology', null],
            ],
            'Digestive System' => [
                ['Non-ulcer dyspepsia (gastritis, bloating, hyperacidity)', null],
                ['Abdominal colic', null],
                ['Gastroesophageal reflux disease (GERD)', null],
                ['Acute gastroenteritis (LBM)', null],
                ['Constipation', null],
                ['Hemorrhoids', null],
                ['Peptic ulcer disease', null],
            ],
            'Urinary System' => [
                ['Kidney stone', null],
                ['Urinary tract infection (UTI)', null],
                ['Pyelonephritis', null],
                ['Cystitis', null],
                ['Urethritis', null],
            ],
            'Immune System' => [
                ['Herpes zoster', null],
                ['Systemic viral infection (SVI)', null],
                ['Chicken pox', null],
                ['Measles', null],
                ['Mumps', null],
            ],
            'Skeletal System' => [
                ['Fracture', null],
                ['Dislocation', null],
                ['Slip disc', null],
                ['Arthritis', null],
            ],
            'Muscular System' => [
                ['Muscle or tendon strain/spasm', null],
                ['Back strain', null],
                ['Shoulder', 'Joint sprain'],
                ['Knee', 'Joint sprain'],
                ['Ankles', 'Joint sprain'],
            ],
            'Nervous System' => [
                ['Cerebral contusion', null],
                ['Benign postural positional vertigo (dizziness, lightheadedness)', null],
                ['Syncope', null],
                ['Headache', null],
                ['Insomnia', null],
                ['Migraine', null],
                ['Epilepsy', null],
                ['Seizure', null],
                ['Neuropathic pain', null],
            ],
            'EENT' => [
                ['Conjunctivitis', null],
                ['Hordeolum', null],
                ['Eye irritation', null],
                ['Foreign object at ear', null],
                ['Otitis media', null],
                ['Otitis externa', null],
                ['Deafness', null],
                ['Impacted cerumen', null],
                ['Neck pain', null],
            ],
            'Reproductive System' => [
                ['Dysmenorrhea', null],
                ['Amenorrhea', null],
                ['Breast lumps', null],
                ['Pregnancy', null],
            ],
            'Integumentary System' => [
                ['Infected wound', null],
                ['Abrasions', null],
                ['Punctured wound', null],
                ['Incised wound', null],
                ['Lacerated wound', null],
                ['Skin lesions', null],
                ['Carbuncle/Abscess', null],
                ['Lymphadenopathy', null],
                ['Soft tissue contusion/Hematoma', null],
                ['Burns', null],
                ['Insect bites/Sting', null],
                ['Animal bites', null],
                ['Viral exanthem (rashes)', null],
                ['Allergies', null],
                ['Eczema', null],
            ],
        ];
    }

    public function run(): void
    {
        $systemOrder = 0;

        foreach (self::list() as $systemName => $illnesses) {
            $system = IllnessSystem::firstOrCreate(['name' => $systemName], ['sort_order' => ++$systemOrder]);

            $order = 0;
            foreach ($illnesses as [$name, $group]) {
                Illness::firstOrCreate(
                    ['illness_system_id' => $system->id, 'name' => $name],
                    ['group_label' => $group, 'is_other' => false, 'sort_order' => ++$order, 'is_active' => true]
                );
            }

            Illness::firstOrCreate(
                ['illness_system_id' => $system->id, 'name' => 'Others'],
                ['group_label' => null, 'is_other' => true, 'sort_order' => 1000, 'is_active' => true]
            );
        }
    }
}
