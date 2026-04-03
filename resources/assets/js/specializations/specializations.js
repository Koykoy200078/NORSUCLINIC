
listenClick('#createSpecialization', function () {
    $('#createSpecializationModal').modal('show').appendTo('body')
})

listen('hidden.bs.modal', '#createSpecializationModal', function () {
    resetModalForm('#createSpecializationForm',
        '#createSpecializationValidationErrorsBox')
})

listen('hidden.bs.modal', '#editSpecializationModal', function () {
    resetModalForm('#editSpecializationForm',
        '#editSpecializationValidationErrorsBox')
})

listenClick('.specialization-edit-btn', function (event) {
    let editSpecializationId = $(event.currentTarget).attr('data-id')
    renderData(editSpecializationId)
})

function renderData (id) {
    $.ajax({
        url: route('specializations.edit', id),
        type: 'GET',
        success: function (result) {
            $('#specializationID').val(result.data.id)
            $('#editName').val(result.data.name)
            $('#editSpecializationModal').modal('show')
        },
    })
}

listenSubmit('#createSpecializationForm', function (e) {
    e.preventDefault()
    let $btn = $('#createSpecializationForm button[type="submit"]')
    $btn.prop('disabled', true)
    $.ajax({
        url: route('specializations.store'),
        type: 'POST',
        data: $(this).serialize(),
        success: function (result) {
            if (result.success) {
                displaySuccessMessage(result.message)
                $('#createSpecializationModal').modal('hide')
                setTimeout(function () {
                    window.location.reload()
                }, 1500)
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message)
            $btn.prop('disabled', false)
        },
    })
})

listenSubmit('#editSpecializationForm', function (e) {
    e.preventDefault()
    let $btn = $('#editSpecializationForm button[type="submit"]')
    $btn.prop('disabled', true)
    let updateSpecializationId = $('#specializationID').val()
    $.ajax({
        url: route('specializations.update', updateSpecializationId),
        type: 'PUT',
        data: $(this).serialize(),
        success: function (result) {
            $('#editSpecializationModal').modal('hide')
            displaySuccessMessage(result.message)
            setTimeout(function () {
                window.location.reload()
            }, 1500)
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message)
            $btn.prop('disabled', false)
        },
    })
})

listenClick('.specialization-delete-btn', function (event) {
    let specializationRecordId = $(event.currentTarget).attr('data-id')
    deleteItem(route('specializations.destroy', specializationRecordId), Lang.get('js.specializations'))
})
