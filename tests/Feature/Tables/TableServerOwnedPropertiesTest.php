<?php

namespace Tests\Feature\Tables;

use App\Livewire\BarangayTable;
use App\Livewire\DoctorTable;
use App\Livewire\MedicineCategoryTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * A list's header (its "Add" button view, its filter view and labels, its empty message, its table name) is set by the server.
 * Livewire lets a browser change any public property it is not told to refuse, and the list draws `@include($this->buttonComponent)`
 * and `@include($this->FilterComponent[0])`: a hand-edited request could make the server draw any view in the application in
 * the list's header. The list base refuses those changes, as #[Locked] would (a list that declares the property again would
 * lose a #[Locked] put on the base). What a person changes on a list (search, sort, page size, page) stays open.
 */
class TableServerOwnedPropertiesTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccessControl();
        $this->actingAs($this->makeAdmin());
    }

    /** @return array<string, array{class-string, string, mixed}> */
    public static function serverOwnedProperties(): array
    {
        return [
            'add-button view' => [BarangayTable::class, 'buttonComponent', 'livewire.loading_skeleton'],
            'add-button switch' => [BarangayTable::class, 'showButtonOnHeader', false],
            'filter view' => [DoctorTable::class, 'FilterComponent', ['livewire.loading_skeleton', []]],
            'one entry of the filter' => [MedicineCategoryTable::class, 'FilterComponent.0', 'livewire.loading_skeleton'],
            'filter switch' => [DoctorTable::class, 'showFilterOnHeader', false],
            'empty message' => [BarangayTable::class, 'emptyMessage', 'Something else'],
            'public table name' => [DoctorTable::class, 'tableName', 'other'],
        ];
    }

    #[DataProvider('serverOwnedProperties')]
    public function test_a_browser_cannot_change_what_the_header_of_a_list_draws(string $list, string $property, mixed $value): void
    {
        $component = Livewire::withoutLazyLoading()->test($list);

        $this->expectException(CannotUpdateLockedPropertyException::class);

        $component->set($property, $value);
    }

    public function test_search_sort_page_size_and_page_stay_open_to_the_browser(): void
    {
        Livewire::withoutLazyLoading()->test(BarangayTable::class)
            ->set('search', 'San')
            ->set('perPage', 25)
            ->call('sortBy', 'name')
            ->call('gotoPage', 2)
            ->assertSet('search', 'San')
            ->assertSet('perPage', 25)
            ->assertSet('sorts', ['name' => 'asc']);
    }
}
