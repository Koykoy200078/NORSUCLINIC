document.addEventListener('DOMContentLoaded', loadSliderData)

function loadSliderData () {
    if (!$('#shortDescription').length) {
        return
    }

    listenKeyup('#shortDescription', function () {
        $('#sliderShortDescription').attr('maxlength', 55)
    })

    if (!$('#sliderShortDescription').length) {
        return
    }

    $('#sliderShortDescription').attr('maxlength', 55)
}
