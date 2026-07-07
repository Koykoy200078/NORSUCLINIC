// Dashboard "Recent Patients" tabs (day / week / month) for the admin dashboard.
//
// The revenue / "Total Earning" charts, per-month currency totals, radial performance
// gauges, the doctor appointment ApexChart, and the service/doctor/category revenue filters
// were REMOVED — the clinic app is de-monetized and none of those elements are rendered by
// any blade anymore (verified: #adminChartData/#patientChartData/#doctorChartData,
// .totalEarning/.total-amount/.*-month-total-amount/.js-radial/#serviceId all have zero
// blade references). Only the non-revenue patient-list tab handlers remain.

listenClick("#monthData", function (e) {
    e.preventDefault();
    $.ajax({
        url: route("patientData.dashboard"),
        type: "GET",
        data: { month: "month" },
        success: function (result) {
            if (result.success) {
                $("#monthlyReport").empty();
                $(document).find("#week").removeClass("show active");
                $(document).find("#day").removeClass("show active");
                $(document).find("#month").addClass("show active");
                if (result.data.patients.data != "") {
                    $.each(result.data.patients.data, function (index, value) {
                        let data = [
                            {
                                image: value.profile,
                                name: value.user.full_name,
                                email: value.user.email,
                                patientId:
                                    value.user.university_id_number ||
                                    value.patient_unique_id,
                                registered: moment
                                    .parseZone(value.user.created_at)
                                    .format("Do MMM Y hh:mm A"),
                                route: route("patients.show", value.id),
                            },
                        ];
                        $(document)
                            .find("#monthlyReport")
                            .append(
                                prepareTemplateRender(
                                    "#adminDashboardTemplate",
                                    data,
                                ),
                            );
                    });
                } else {
                    $(document).find("#monthlyReport")
                        .append(`<tr class="text-center">
                                                    <td colspan="3" class="text-muted fw-bold">${noData}</td>
                                                </tr>`);
                }
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick("#weekData", function (e) {
    e.preventDefault();
    $.ajax({
        url: route("patientData.dashboard"),
        type: "GET",
        data: { week: "week" },
        success: function (result) {
            if (result.success) {
                $("#weeklyReport").empty();
                $(document).find("#month").removeClass("show active");
                $(document).find("#day").removeClass("show active");
                $(document).find("#week").addClass("show active");
                if (result.data.patients.data != "") {
                    $.each(result.data.patients.data, function (index, value) {
                        let data = [
                            {
                                image: value.profile,
                                name: value.user.full_name,
                                email: value.user.email,
                                patientId:
                                    value.user.university_id_number ||
                                    value.patient_unique_id,
                                registered: moment
                                    .parseZone(value.user.created_at)
                                    .format("Do MMM Y hh:mm A"),
                                route: route("patients.show", value.id),
                            },
                        ];
                        $(document)
                            .find("#weeklyReport")
                            .append(
                                prepareTemplateRender(
                                    "#adminDashboardTemplate",
                                    data,
                                ),
                            );
                    });
                } else {
                    $(document).find("#weeklyReport")
                        .append(`<tr class="text-center">
                                                    <td colspan="3" class="text-muted fw-bold">${noData}</td>
                                                </tr>`);
                }
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick("#dayData", function (e) {
    e.preventDefault();
    $.ajax({
        url: route("patientData.dashboard"),
        type: "GET",
        data: { day: "day" },
        success: function (result) {
            if (result.success) {
                $("#dailyReport").empty();
                $(document).find("#month").removeClass("show active");
                $(document).find("#week").removeClass("show active");
                $(document).find("#day").addClass("show active");
                if (result.data.patients.data != "") {
                    $.each(result.data.patients.data, function (index, value) {
                        let data = [
                            {
                                image: value.profile,
                                name: value.user.full_name,
                                email: value.user.email,
                                patientId:
                                    value.user.university_id_number ||
                                    value.patient_unique_id,
                                registered: moment
                                    .parseZone(value.user.created_at)
                                    .format("Do MMM Y hh:mm A"),
                                route: route("patients.show", value.id),
                            },
                        ];
                        $(document)
                            .find("#dailyReport")
                            .append(
                                prepareTemplateRender(
                                    "#adminDashboardTemplate",
                                    data,
                                ),
                            );
                    });
                } else {
                    $(document).find("#dailyReport").append(`
                    <tr class="text-center">
                        <td colspan="3" class="text-muted fw-bold"> ${noData}</td>
                    </tr>`);
                }
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
});

listenClick(".dayData", function () {
    $(this).addClass("text-primary");
    $(".weekData ,.monthData").removeClass("text-primary");
});
listenClick(".weekData", function () {
    $(this).addClass("text-primary");
    $(".dayData ,.monthData").removeClass("text-primary");
});
listenClick(".monthData", function () {
    $(this).addClass("text-primary");
    $(".weekData ,.dayData").removeClass("text-primary");
});
