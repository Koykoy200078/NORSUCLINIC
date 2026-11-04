<?php

namespace Tests\Concerns;

use App\Models\Barangay;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Generic;
use App\Models\Medicine;
use App\Models\Role;
use App\Models\Slider;
use App\Models\State;
use Carbon\Carbon;
use Livewire\Features\SupportTesting\Testable;

/**
 * The fixed data behind the list-screen golden master (TableGoldenMasterTest): 35 rows per list, so the lists have four
 * pages of 10 and two of 25, named so that one word ("Zeta") finds exactly every tenth row. Created time stamps are
 * explicit (one minute apart) so the default order, the newest first, is the same on every run.
 *
 * Each seedXxx() method returns the mount() parameters of the list and the word the search scenario types.
 */
trait SeedsTableLists
{
    protected const SEARCH_WORD = 'Zeta';

    protected const ROWS = 35;

    /** "Alpha Brgy 01" ... "Zeta Brgy 05" ...: every tenth row (5, 15, 25, 35) carries the search word. */
    protected function label(string $what, int $i): string
    {
        return ($i % 10 === 5 ? 'Zeta ' : 'Alpha ') . $what . ' ' . sprintf('%02d', $i);
    }

    /** @return array<string, Carbon> */
    protected function stamps(int $i): array
    {
        $at = Carbon::parse('2026-02-01 08:00:00')->addMinutes($i);

        return ['created_at' => $at, 'updated_at' => $at];
    }

    /** @return array{params: array<string, mixed>, search: string} */
    protected function seeded(array $params = []): array
    {
        return ['params' => $params, 'search' => self::SEARCH_WORD];
    }

    protected function seedCountriesTable(): array
    {
        for ($i = 1; $i <= self::ROWS; $i++) {
            Country::create(['name' => $this->label('Land', $i), 'short_code' => sprintf('C%02d', $i)] + $this->stamps($i));
        }

        return $this->seeded();
    }

    protected function seedStateTable(): array
    {
        $countries = [Country::create(['name' => 'Philippines', 'short_code' => 'PH']), Country::create(['name' => 'Japan', 'short_code' => 'JP'])];
        for ($i = 1; $i <= self::ROWS; $i++) {
            State::create(['name' => $this->label('State', $i), 'country_id' => $countries[$i % 2]->id] + $this->stamps($i));
        }

        return $this->seeded();
    }

    protected function seedCityTable(): array
    {
        $country = Country::create(['name' => 'Philippines', 'short_code' => 'PH']);
        $states = [State::create(['name' => 'Negros Oriental', 'country_id' => $country->id]), State::create(['name' => 'Cebu', 'country_id' => $country->id])];
        for ($i = 1; $i <= self::ROWS; $i++) {
            City::create(['name' => $this->label('City', $i), 'type' => $i % 3 === 0 ? 'Municipality' : 'City', 'state_id' => $states[$i % 2]->id] + $this->stamps($i));
        }

        return $this->seeded();
    }

    protected function seedBarangayTable(): array
    {
        $country = Country::create(['name' => 'Philippines', 'short_code' => 'PH']);
        $state = State::create(['name' => 'Negros Oriental', 'country_id' => $country->id]);
        $cities = [City::create(['name' => 'Dumaguete', 'type' => 'City', 'state_id' => $state->id]), City::create(['name' => 'Bacong', 'type' => 'Municipality', 'state_id' => $state->id])];
        for ($i = 1; $i <= self::ROWS; $i++) {
            Barangay::create(['name' => $this->label('Brgy', $i), 'city_id' => $cities[$i % 2]->id] + $this->stamps($i));
        }

        return $this->seeded();
    }

    protected function seedSliderTable(): array
    {
        for ($i = 1; $i <= self::ROWS; $i++) {
            Slider::create(['title' => $this->label('Slide', $i), 'short_description' => 'About slide ' . sprintf('%02d', $i)] + $this->stamps($i));
        }

        return $this->seeded();
    }

    protected function seedRoleTable(): array
    {
        for ($i = 1; $i <= self::ROWS; $i++) {
            Role::create(['name' => 'golden_role_' . $i, 'guard_name' => 'web', 'display_name' => $this->label('Role', $i)] + $this->stamps($i));
        }

        return $this->seeded();
    }

    protected function seedSpecializationTable(): array
    {
        for ($i = 1; $i <= self::ROWS; $i++) {
            \App\Models\Specialization::create(['name' => $this->label('Specialty', $i)] + $this->stamps($i));
        }

        return $this->seeded();
    }

    protected function seedMedicineCategoryTable(): array
    {
        for ($i = 1; $i <= self::ROWS; $i++) {
            Category::create(['name' => $this->label('Category', $i), 'is_active' => $i % 4 === 0 ? 0 : 1] + $this->stamps($i));
        }

        return $this->seeded() + ['extras' => [
            // the header filter dispatches this event (resources/assets/js/category/category.js)
            'status: active' => fn (Testable $component) => $component->dispatch('changeFilter', value: 1),
            'status: inactive' => fn (Testable $component) => $component->dispatch('changeFilter', value: 2),
        ]];
    }

    protected function seedMedicineGenericTable(): array
    {
        for ($i = 1; $i <= self::ROWS; $i++) {
            Generic::create(['name' => $this->label('Generic', $i)] + $this->stamps($i));
        }

        return $this->seeded();
    }

    /** Medicines of one category and one generic, so the two "details" lists have a parent to show. */
    private function medicinesOfOneParent(): array
    {
        $category = Category::create(['name' => 'Analgesics', 'is_active' => 1]);
        $generic = Generic::create(['name' => 'Paracetamol']);
        $other = Category::create(['name' => 'Antibiotics', 'is_active' => 1]);
        for ($i = 1; $i <= self::ROWS; $i++) {
            Medicine::create([
                'name' => $this->label('Med', $i), 'category' => 'General', 'quantity' => 0, 'available_quantity' => 0, 'dosage' => '500mg',
                'category_id' => $category->id, 'generic_id' => $generic->id, 'description' => 'For case ' . sprintf('%02d', $i),
            ] + $this->stamps($i));
        }
        // a medicine of another category and generic: it must never show in the lists of the first one
        Medicine::create(['name' => 'Zeta Stranger', 'category' => 'General', 'quantity' => 0, 'available_quantity' => 0, 'dosage' => '500mg', 'category_id' => $other->id, 'generic_id' => Generic::create(['name' => 'Amoxicillin'])->id]);

        return [$category, $generic];
    }

    protected function seedMedicineCategoryDetailsTable(): array
    {
        [$category] = $this->medicinesOfOneParent();

        return $this->seeded(['categoryDetails' => (string) $category->id]);
    }

    protected function seedMedicineGenericDetailsTable(): array
    {
        [, $generic] = $this->medicinesOfOneParent();

        return $this->seeded(['genericDetails' => (string) $generic->id]);
    }
}
