document.addEventListener("DOMContentLoaded", loadSettingData);

let form;
let loadData = false;

function loadSettingData() {
    let settingCountryId = $("#settingCountryId").val();
    let settingStateId = $("#settingStateId").val();
    let settingCityId = $("#settingCityId").val();
    if (settingCountryId != "") {
        $("#settingCountryId").val(settingCountryId).trigger("change");

        setTimeout(function () {
            $("#settingStateId").val(settingStateId).trigger("change");
        }, 800);

        setTimeout(function () {
            $("#settingCityId").val(settingCityId).trigger("change");
        }, 400);

        loadData = true;
    }

    if (!$("#generalSettingForm").length) {
        return;
    }

    form = document.getElementById("generalSettingForm");
}

listenChange("#settingCountryId", function () {
    $.ajax({
        url: panelRoute("states-list"),
        type: "get",
        dataType: "json",
        data: { settingCountryId: $(this).val() },
        success: function (data) {
            $("#settingStateId").empty();
            $("#settingCityId").empty();
            $("#settingStateId").append(
                $('<option value=""></option>').text(
                    Lang.get("js.select_state"),
                ),
            );
            $("#settingCityId").append(
                $('<option value=""></option>').text(
                    Lang.get("js.select_city"),
                ),
            );
            $.each(data.data.states, function (i, v) {
                $("#settingStateId").append(
                    $(
                        `<option ${
                            !loadData && i == data.data.state_id
                                ? "selected"
                                : ""
                        }></option>`,
                    )
                        .attr("value", i)
                        .text(v),
                );
            });
        },
    });
});

listenChange("#settingStateId", function () {
    $("#settingCityId").empty();
    $.ajax({
        url: panelRoute("cities-list"),
        type: "get",
        dataType: "json",
        data: { stateId: $(this).val() },
        success: function (data) {
            $("#settingCityId").empty();
            $("#settingCityId").append(
                $('<option value=""></option>').text(
                    Lang.get("js.select_city"),
                ),
            );
            $.each(data.data.cities, function (i, v) {
                $("#settingCityId").append(
                    $(
                        `<option ${
                            loadData && i == data.data.city_id ? "selected" : ""
                        }></option>`,
                    )
                        .attr("value", i)
                        .text(v),
                );
            });
        },
    });
});

listenClick("#settingSubmitBtn", function () {
    let settingForm = $("#generalSettingForm")[0];

    // Philippine number check (+63) - shows the inline error and focuses the field.
    if (!window.PhPhone.validateForm(settingForm)) {
        return false;
    }

    settingForm.submit();
});
