<?php

namespace Tests\Feature\Regression;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsClinicData;
use Tests\TestCase;

/**
 * Found by the form-builder swap (U0.3) when the link crawl got a medicine category to open: the "Back" button of the category
 * page was hard-wired to the administrator's list, so a Staff (Nurse) or a doctor who followed it got a 403.
 */
class CategoryPageBackLinkTest extends TestCase
{
    use RefreshDatabase;
    use BuildsClinicData;

    public function test_the_back_button_of_the_category_page_goes_to_the_list_of_the_users_own_panel(): void
    {
        $this->seedAccessControl();
        $category = Category::create(['name' => 'Analgesics', 'is_active' => 1]);

        $cases = [
            'admin' => [$this->makeAdmin(), 'categories.show', 'categories.index'],
            'staff' => [$this->makeNurse(), 'staff.categories.show', 'staff.categories.index'],
            'doctor' => [$this->makeDoctor(), 'doctors.categories.show', 'doctors.categories.index'],
        ];

        foreach ($cases as $who => [$user, $page, $list]) {
            $response = $this->actingAs($user)->get(route($page, $category->id))->assertOk();

            $this->assertStringContainsString('href="' . route($list) . '"', $response->getContent(), "the {$who}'s Back button goes to their own list");
        }
    }
}
