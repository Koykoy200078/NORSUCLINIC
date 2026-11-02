<!-- Name Field -->
<div class="form-group col-sm-6">
    {{ html()->label()->for('name')->text(__('messages.medicine.category')) }}
    {{ html()->text('name', null)->attributes(['class' => 'form-control']) }}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {{ html()->input('submit', null, 'Save')->attributes(['class' => 'btn btn-primary']) }}
    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Cancel</a>
</div>
