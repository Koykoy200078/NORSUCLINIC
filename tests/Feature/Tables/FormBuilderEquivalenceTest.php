<?php

namespace Tests\Feature\Tables;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ReadsFormControls;
use Tests\TestCase;

/**
 * Upgrade plan U0.3: every way the views call the old form builder (Collective, `Form::`) next to its spatie/laravel-html
 * equivalent (`html()->`), drawn with the same variables, session and model, and compared control by control.
 *
 * The right-hand column of each case IS the conversion rule: the converter used on the 83 views produces exactly these
 * shapes (a field gets an `id` only where Collective wrote one; `placeholder` and `multiple` of a select become method calls;
 * Form::submit is an <input>; Form::textarea keeps cols 50 / rows 10; labels escape their text). A rule that is wrong shows
 * up here as a difference. This test needs both packages: it goes away in the same stage that removes Collective.
 */
class FormBuilderEquivalenceTest extends TestCase
{
    use RefreshDatabase;
    use ReadsFormControls;

    /** @param array<string, mixed> $old values flashed by a failed submit */
    private function draw(string $template, array $vars, array $old): string
    {
        $session = app('session.store');
        $session->start();
        $session->flush();
        $session->put('_old_input', $old);
        request()->setLaravelSession($session);

        return Blade::render($template, $vars);
    }

    /**
     * @param array<string, mixed> $vars
     * @param array<string, mixed> $old
     */
    private function assertSameControls(string $collective, string $spatie, array $vars = [], array $old = []): void
    {
        $was = $this->formControls($this->draw($collective, $vars, $old));
        $now = $this->formControls($this->draw($spatie, $vars, $old));

        $this->assertNotSame([], $was, 'the old call drew nothing');
        $this->assertSame($was, $now, "the conversion draws something else\n  Collective: {$collective}\n  spatie:     {$spatie}");
    }

    /** @param array<string, mixed> $attributes */
    private static function model(array $attributes): Model
    {
        return new class($attributes) extends Model {
            protected $guarded = [];

            protected $casts = ['tags' => 'array', 'address' => 'array'];
        };
    }

    /** @return array<string, array{0: string, 1: string, 2?: array<string, mixed>, 3?: array<string, mixed>}> */
    public static function cases(): array
    {
        $rows = ['1' => 'Alpha', '2' => 'Beta & Co', '3' => 'Gamma <3>'];
        $groups = ['Group A' => ['a1' => 'One', 'a2' => 'Two'], 'Group B' => ['b1' => 'Three']];
        $model = ['first_name' => 'Ana', 'notes' => "line one\nline <two>", 'is_active' => 1, 'type' => 2, 'tags' => [1, 3], 'address' => ['city' => 'Dumaguete']];

        return [
            // ---- text-like fields
            'text with value, flags and data attributes, no label' => [
                "{{ Form::text('first_name', \$v, ['class' => 'form-control', 'placeholder' => 'First', 'required', 'data-ph-phone' => 'true']) }}",
                "{{ html()->text('first_name', \$v)->forgetAttribute('id')->attributes(['class' => 'form-control', 'placeholder' => 'First', 'required', 'data-ph-phone' => 'true']) }}",
                ['v' => 'Ana "A" <b>'],
            ],
            'text without value' => [
                "{{ Form::text('first_name', null, ['class' => 'form-control']) }}",
                "{{ html()->text('first_name', null)->forgetAttribute('id')->attributes(['class' => 'form-control']) }}",
            ],
            'text with only a name' => [
                "{{ Form::text('first_name') }}",
                "{{ html()->text('first_name')->forgetAttribute('id') }}",
            ],
            'text after a label of the same name takes the name as its id' => [
                "{{ Form::label('first_name', 'First:') }}{{ Form::text('first_name', 'x', ['class' => 'c']) }}",
                "{{ html()->label()->for('first_name')->text('First:') }}{{ html()->text('first_name', 'x')->attributes(['class' => 'c']) }}",
            ],
            'text with an id of its own wins over the label rule' => [
                "{{ Form::label('first_name', 'First:') }}{{ Form::text('first_name', 'x', ['id' => 'mine']) }}",
                "{{ html()->label()->for('first_name')->text('First:') }}{{ html()->text('first_name', 'x')->attributes(['id' => 'mine']) }}",
            ],
            'a label of another name gives no id' => [
                "{{ Form::label('other', 'Other') }}{{ Form::text('first_name', 'x') }}",
                "{{ html()->label()->for('other')->text('Other') }}{{ html()->text('first_name', 'x')->forgetAttribute('id') }}",
            ],
            'hidden' => [
                "{{ Form::hidden('patient_id', 5) }}{{ Form::hidden('note', null, ['id' => 'noteId']) }}",
                "{{ html()->hidden('patient_id', 5)->forgetAttribute('id') }}{{ html()->hidden('note', null)->attributes(['id' => 'noteId']) }}",
            ],
            'email' => [
                "{{ Form::email('email', 'a@b.test', ['class' => 'form-control', 'required']) }}",
                "{{ html()->email('email', 'a@b.test')->forgetAttribute('id')->attributes(['class' => 'form-control', 'required']) }}",
            ],
            'number with min max' => [
                "{{ Form::number('qty', 3, ['class' => 'q', 'min' => 1, 'max' => '10', 'step' => 'any']) }}",
                "{{ html()->number('qty', 3)->forgetAttribute('id')->attributes(['class' => 'q', 'min' => 1, 'max' => '10', 'step' => 'any']) }}",
            ],
            'password has no value' => [
                "{{ Form::password('password', ['class' => 'form-control', 'autocomplete' => 'new-password']) }}",
                "{{ html()->password('password')->forgetAttribute('id')->attributes(['class' => 'form-control', 'autocomplete' => 'new-password']) }}",
            ],
            'date from a date string' => [
                "{{ Form::date('d', '2026-03-04', ['class' => 'x']) }}",
                "{{ html()->date('d', '2026-03-04')->forgetAttribute('id')->attributes(['class' => 'x']) }}",
            ],
            'date from a Carbon value' => [
                "{{ Form::date('d', \$c, ['class' => 'x']) }}",
                "{{ html()->date('d', \$c)->forgetAttribute('id')->attributes(['class' => 'x']) }}",
                ['c' => \Carbon\Carbon::parse('2026-03-04 10:11:12')],
            ],
            'date with no value' => [
                "{{ Form::date('d', null, ['class' => 'x']) }}",
                "{{ html()->date('d', null)->forgetAttribute('id')->attributes(['class' => 'x']) }}",
            ],

            // ---- textarea
            'textarea keeps Collective\'s default size' => [
                "{{ Form::textarea('notes', \$v, ['class' => 'form-control']) }}",
                "{{ html()->textarea('notes')->text(html()->value('notes', \$v))->forgetAttribute('id')->attributes(['cols' => 50, 'rows' => 10])->attributes(['class' => 'form-control']) }}",
                ['v' => "line one\nline <two> & three"],
            ],
            'textarea with its own rows' => [
                "{{ Form::textarea('notes', null, ['rows' => 3, 'class' => 'c']) }}",
                "{{ html()->textarea('notes')->text(html()->value('notes', null))->forgetAttribute('id')->attributes(['cols' => 50])->attributes(['rows' => 3, 'class' => 'c']) }}",
            ],
            'textarea with its own rows and cols' => [
                "{{ Form::textarea('notes', null, ['rows' => 3, 'cols' => 20]) }}",
                "{{ html()->textarea('notes')->text(html()->value('notes', null))->forgetAttribute('id')->attributes(['rows' => 3, 'cols' => 20]) }}",
            ],

            // ---- select
            'select with a selected value' => [
                "{{ Form::select('type', \$rows, '2', ['class' => 'form-select', 'data-control' => 'select2']) }}",
                "{{ html()->select('type', \$rows, '2')->forgetAttribute('id')->attributes(['class' => 'form-select', 'data-control' => 'select2']) }}",
                ['rows' => $rows],
            ],
            'select with an integer selected against string keys' => [
                "{{ Form::select('type', \$rows, 2) }}",
                "{{ html()->select('type', \$rows, 2)->forgetAttribute('id') }}",
                ['rows' => $rows],
            ],
            'select with nothing selected' => [
                "{{ Form::select('type', \$rows, null, ['class' => 'form-select']) }}",
                "{{ html()->select('type', \$rows, null)->forgetAttribute('id')->attributes(['class' => 'form-select']) }}",
                ['rows' => $rows],
            ],
            'select with a placeholder and nothing selected' => [
                "{{ Form::select('type', \$rows, null, ['class' => 'form-select', 'placeholder' => 'Pick one']) }}",
                "{{ html()->select('type', \$rows, null)->placeholder('Pick one')->forgetAttribute('id')->attributes(['class' => 'form-select']) }}",
                ['rows' => $rows],
            ],
            'select with a placeholder and a value selected' => [
                "{{ Form::select('type', \$rows, '3', ['placeholder' => 'Pick & choose']) }}",
                "{{ html()->select('type', \$rows, '3')->placeholder('Pick & choose')->forgetAttribute('id') }}",
                ['rows' => $rows],
            ],
            'select with a placeholder and an empty-string selection' => [
                "{{ Form::select('type', \$rows, '', ['placeholder' => 'Pick']) }}",
                "{{ html()->select('type', \$rows, '')->placeholder('Pick')->forgetAttribute('id') }}",
                ['rows' => $rows],
            ],
            'select with an id and a placeholder' => [
                "{{ Form::select('state_id', \$rows, null, ['id' => 'editStateId', 'required', 'placeholder' => 'Province']) }}",
                "{{ html()->select('state_id', \$rows, null)->placeholder('Province')->attributes(['id' => 'editStateId', 'required']) }}",
                ['rows' => $rows],
            ],
            'multiple select with selected values' => [
                "{{ Form::select('tags[]', \$rows, [1, 3], ['class' => 'io-select2', 'multiple', 'data-placeholder' => 'Tags']) }}",
                "{{ html()->select('tags[]', \$rows, old('tags', [1, 3]))->multiple()->forgetAttribute('id')->attributes(['class' => 'io-select2', 'data-placeholder' => 'Tags']) }}",
                ['rows' => $rows],
            ],
            'multiple select with nothing selected' => [
                "{{ Form::select('tags[]', \$rows, null, ['multiple']) }}",
                "{{ html()->select('tags[]', \$rows, old('tags', null))->multiple()->forgetAttribute('id') }}",
                ['rows' => $rows],
            ],
            'multiple select given a Collection of selected ids' => [
                "{{ Form::select('tags[]', \$rows, \$sel, ['multiple']) }}",
                "{{ html()->select('tags[]', \$rows, old('tags', \$sel))->multiple()->forgetAttribute('id') }}",
                ['rows' => $rows, 'sel' => new Collection([2, 3])],
            ],
            'select options as a Collection' => [
                "{{ Form::select('type', \$col, 'b') }}",
                "{{ html()->select('type', \$col, 'b')->forgetAttribute('id') }}",
                ['col' => new Collection(['a' => 'Aye', 'b' => 'Bee'])],
            ],
            'select with groups of options' => [
                "{{ Form::select('grp', \$groups, 'b1') }}",
                "{{ html()->select('grp', \$groups, 'b1')->forgetAttribute('id') }}",
                ['groups' => $groups],
            ],
            'select with no options' => [
                "{{ Form::select('city_id', [], null, ['placeholder' => 'City']) }}",
                "{{ html()->select('city_id', [], null)->placeholder('City')->forgetAttribute('id') }}",
            ],
            'select after a label of the same name takes the name as its id' => [
                "{{ Form::label('type', 'Type') }}{{ Form::select('type', \$rows, null, ['class' => 'x']) }}",
                "{{ html()->label()->for('type')->text('Type') }}{{ html()->select('type', \$rows, null)->attributes(['class' => 'x']) }}",
                ['rows' => $rows],
            ],

            // ---- checkbox, radio
            'checkbox unchecked' => [
                "{{ Form::checkbox('is_active', 1, false, ['class' => 'form-check-input']) }}",
                "{{ html()->checkbox('is_active', false, 1)->forgetAttribute('id')->attributes(['class' => 'form-check-input']) }}",
            ],
            'checkbox checked' => [
                "{{ Form::checkbox('is_active', 'yes', true) }}",
                "{{ html()->checkbox('is_active', true, 'yes')->forgetAttribute('id') }}",
            ],
            'checkbox from a model attribute value' => [
                "{{ Form::checkbox('is_active', 1, \$user->is_active == 1) }}",
                "{{ html()->checkbox('is_active', \$user->is_active == 1, 1)->forgetAttribute('id') }}",
                ['user' => (object) ['is_active' => 1]],
            ],
            'radio unchecked and checked' => [
                "{{ Form::radio('gender', 'male', false, ['id' => 'm']) }}{{ Form::radio('gender', 'female', true, ['id' => 'f']) }}",
                "{{ html()->radio('gender', false, 'male')->attributes(['id' => 'm']) }}{{ html()->radio('gender', true, 'female')->attributes(['id' => 'f']) }}",
            ],

            // ---- label
            'label with text and class' => [
                "{{ Form::label('first_name', 'First name:', ['class' => 'form-label required']) }}",
                "{{ html()->label()->for('first_name')->text('First name:')->attributes(['class' => 'form-label required']) }}",
            ],
            'label text is escaped' => [
                "{{ Form::label('x', 'Fish & <i>Chips</i>') }}",
                "{{ html()->label()->for('x')->text('Fish & <i>Chips</i>') }}",
            ],
            'label for a human-readable name' => [
                "{{ Form::label('First Name', 'First name:') }}",
                "{{ html()->label()->for('First Name')->text('First name:') }}",
            ],
            'label with no text shows the name in words' => [
                "{{ Form::label('first_name') }}",
                "{{ html()->label()->for('first_name')->text('First Name') }}",
            ],

            // ---- buttons
            'submit' => [
                "{{ Form::submit('Save', ['class' => 'btn btn-primary', 'id' => 'saveBtn']) }}",
                "{{ html()->input('submit', null, 'Save')->attributes(['class' => 'btn btn-primary', 'id' => 'saveBtn']) }}",
            ],
            'button with markup inside' => [
                "{{ Form::button('<i class=\"fa fa-x\"></i> Cancel', ['class' => 'btn', 'data-bs-dismiss' => 'modal']) }}",
                "{{ html()->button('<i class=\"fa fa-x\"></i> Cancel', 'button')->attributes(['class' => 'btn', 'data-bs-dismiss' => 'modal']) }}",
            ],
            'button of another type' => [
                "{{ Form::button('Go', ['type' => 'submit', 'class' => 'btn']) }}",
                "{{ html()->button('Go', 'button')->attributes(['type' => 'submit', 'class' => 'btn']) }}",
            ],

            // ---- form open / close
            'open with an id (no route)' => [
                "{{ Form::open(['id' => 'createForm']) }}{{ Form::close() }}",
                "{{ html()->form('POST', url()->current())->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'createForm'])->open() }}{{ html()->closeModelForm() }}",
            ],
            'open with a route' => [
                "{{ Form::open(['route' => 'login']) }}{{ Form::close() }}",
                "{{ html()->form('POST', route('login'))->attribute('accept-charset', 'UTF-8')->open() }}{{ html()->closeModelForm() }}",
            ],
            'open with a route and a parameter, a spoofed method and files' => [
                "{{ Form::open(['route' => ['login', 5], 'method' => 'put', 'files' => true, 'class' => 'f']) }}{{ Form::close() }}",
                "{{ html()->form('put', route('login', [5]))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->attributes(['class' => 'f'])->open() }}{{ html()->closeModelForm() }}",
            ],
            'open with a string for files' => [
                "{{ Form::open(['route' => 'login', 'files' => 'true']) }}{{ Form::close() }}",
                "{{ html()->form('POST', route('login'))->attribute('accept-charset', 'UTF-8')->acceptsFiles()->open() }}{{ html()->closeModelForm() }}",
            ],
            'open as delete with data attributes' => [
                "{{ Form::open(['route' => 'login', 'method' => 'delete', 'data-turbo' => 'false', 'id' => 'del']) }}{{ Form::close() }}",
                "{{ html()->form('delete', route('login'))->attribute('accept-charset', 'UTF-8')->attributes(['data-turbo' => 'false', 'id' => 'del'])->open() }}{{ html()->closeModelForm() }}",
            ],
            'open as GET has no token' => [
                "{{ Form::open(['route' => 'login', 'method' => 'get']) }}{{ Form::close() }}",
                "{{ html()->form('get', route('login'))->attribute('accept-charset', 'UTF-8')->open() }}{{ html()->closeModelForm() }}",
            ],
            'open with a url' => [
                "{{ Form::open(['url' => '/somewhere']) }}{{ Form::close() }}",
                "{{ html()->form('POST', url('/somewhere'))->attribute('accept-charset', 'UTF-8')->open() }}{{ html()->closeModelForm() }}",
            ],

            // ---- model binding
            'model form fills fields from the model' => [
                "{{ Form::model(\$m, ['route' => ['login', 7], 'method' => 'patch', 'id' => 'edit']) }}"
                    . "{{ Form::label('first_name', 'First') }}{{ Form::text('first_name', null, ['class' => 'a']) }}"
                    . "{{ Form::textarea('notes', null, ['class' => 'b']) }}{{ Form::select('type', \$rows, null, ['class' => 'c']) }}"
                    . "{{ Form::checkbox('is_active', 1, null) }}{{ Form::select('tags[]', \$rows, null, ['multiple']) }}{{ Form::close() }}",
                "{{ html()->modelForm(\$m, 'patch', route('login', [7]))->attribute('accept-charset', 'UTF-8')->attributes(['id' => 'edit'])->open() }}"
                    . "{{ html()->label()->for('first_name')->text('First') }}{{ html()->text('first_name', null)->attributes(['class' => 'a']) }}"
                    . "{{ html()->textarea('notes')->text(html()->value('notes', null))->forgetAttribute('id')->attributes(['cols' => 50, 'rows' => 10])->attributes(['class' => 'b']) }}{{ html()->select('type', \$rows, null)->forgetAttribute('id')->attributes(['class' => 'c']) }}"
                    . "{{ html()->checkbox('is_active', null, 1)->forgetAttribute('id') }}{{ html()->select('tags[]', \$rows, old('tags', data_get(\$m, 'tags')))->multiple()->forgetAttribute('id') }}{{ html()->closeModelForm() }}",
                ['m' => self::model($model), 'rows' => $rows],
            ],
            'the model is forgotten after the form is closed' => [
                "{{ Form::model(\$m, ['url' => '/x']) }}{{ Form::close() }}{{ Form::text('first_name') }}",
                "{{ html()->modelForm(\$m, 'POST', url('/x'))->attribute('accept-charset', 'UTF-8')->open() }}{{ html()->closeModelForm() }}{{ html()->text('first_name')->forgetAttribute('id') }}",
                ['m' => self::model($model)],
            ],
            'a value given in the call wins over the model' => [
                "{{ Form::model(\$m, ['url' => '/x']) }}{{ Form::text('first_name', 'Given') }}{{ Form::close() }}",
                "{{ html()->modelForm(\$m, 'POST', url('/x'))->attribute('accept-charset', 'UTF-8')->open() }}{{ html()->text('first_name', 'Given')->forgetAttribute('id') }}{{ html()->closeModelForm() }}",
                ['m' => self::model($model)],
            ],
            'a nested model value (dot notation)' => [
                "{{ Form::model(\$m, ['url' => '/x']) }}{{ Form::text('address[city]') }}{{ Form::close() }}",
                "{{ html()->modelForm(\$m, 'POST', url('/x'))->attribute('accept-charset', 'UTF-8')->open() }}{{ html()->text('address[city]')->forgetAttribute('id') }}{{ html()->closeModelForm() }}",
                ['m' => self::model($model)],
            ],

            // ---- old input (a failed submit) beats the value in the call and the model
            'old input beats the given value' => [
                "{{ Form::text('first_name', 'Given') }}{{ Form::textarea('notes', 'Given') }}{{ Form::select('type', \$rows, '1') }}{{ Form::email('email', 'given@x.test') }}",
                "{{ html()->text('first_name', 'Given')->forgetAttribute('id') }}{{ html()->textarea('notes')->text(html()->value('notes', 'Given'))->forgetAttribute('id')->attributes(['cols' => 50, 'rows' => 10]) }}{{ html()->select('type', \$rows, '1')->forgetAttribute('id') }}{{ html()->email('email', 'given@x.test')->forgetAttribute('id') }}",
                ['rows' => $rows],
                ['first_name' => 'Typed', 'notes' => 'Typed note', 'type' => '3', 'email' => 'typed@x.test'],
            ],
            'old input beats the model' => [
                "{{ Form::model(\$m, ['url' => '/x']) }}{{ Form::text('first_name') }}{{ Form::select('type', \$rows) }}{{ Form::close() }}",
                "{{ html()->modelForm(\$m, 'POST', url('/x'))->attribute('accept-charset', 'UTF-8')->open() }}{{ html()->text('first_name')->forgetAttribute('id') }}{{ html()->select('type', \$rows)->forgetAttribute('id') }}{{ html()->closeModelForm() }}",
                ['m' => self::model($model), 'rows' => $rows],
                ['first_name' => 'Typed', 'type' => '3'],
            ],
            'old input for a multiple select and a checkbox' => [
                "{{ Form::select('tags[]', \$rows, [1], ['multiple']) }}{{ Form::checkbox('is_active', 1, false) }}",
                "{{ html()->select('tags[]', \$rows, old('tags', [1]))->multiple()->forgetAttribute('id') }}{{ html()->checkbox('is_active', false, 1)->forgetAttribute('id') }}",
                ['rows' => $rows],
                ['tags' => ['2', '3'], 'is_active' => '1'],
            ],
            'old input leaves a password empty' => [
                "{{ Form::password('password') }}",
                "{{ html()->password('password')->forgetAttribute('id') }}",
                [],
                ['password' => 'secret'],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $vars
     * @param array<string, mixed> $old
     */
    #[DataProvider('cases')]
    public function test_the_conversion_draws_the_same_controls(string $collective, string $spatie, array $vars = [], array $old = []): void
    {
        $this->assertSameControls($collective, $spatie, $vars, $old);
    }
}
