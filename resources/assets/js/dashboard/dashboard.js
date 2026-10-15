// Dashboard "Recent Patients" tabs (day / week / month) of the admin and staff dashboards.
//
// The revenue / "Total Earning" charts, per-month currency totals, radial performance
// gauges, the doctor appointment ApexChart, and the service/doctor/category revenue filters
// were REMOVED - the clinic app is de-monetized and none of those elements are rendered by
// any blade anymore. Only the non-revenue patient-list tab handlers remain.
//
// The server decides what each row looks like: the link (a staff member without Manage Patients gets none, the page
// would answer 403) and the registration time in the clinic's timezone (a JSON date is UTC and would show 8 hours early).

function loadRecentPatients(range, pane, body) {
    var params = {};
    params[range] = range;

    $.ajax({
        url: panelRoute("patientData.dashboard"),
        type: "GET",
        data: params,
        success: function (result) {
            if (!result.success) {
                return;
            }

            var list = result.data.patients;
            $(body).empty();
            $(document).find("#day, #week, #month").removeClass("show active");
            $(document).find(pane).addClass("show active");

            if (list.data.length) {
                $.each(list.data, function (index, value) {
                    $(body).append(
                        prepareTemplateRender("#adminDashboardTemplate", [
                            {
                                image: value.profile,
                                name: value.user.full_name,
                                email: value.user.email,
                                patientId: value.patient_identifier,
                                registered: value.registered_label,
                                route: value.url,
                            },
                        ]),
                    );
                });

                if (list.total > list.data.length) {
                    $(body).append(
                        `<tr class="text-center"><td colspan="3" class="text-muted fw-bold">Showing the ${list.data.length} newest of ${list.total}</td></tr>`,
                    );
                }
            } else {
                $(body).append(`<tr class="text-center">
                                                    <td colspan="3" class="text-muted fw-bold">${noData}</td>
                                                </tr>`);
            }
        },
        error: function (result) {
            displayErrorMessage(result.responseJSON.message);
        },
    });
}

listenClick("#monthData", function (e) {
    e.preventDefault();
    loadRecentPatients("month", "#month", "#monthlyReport");
});

listenClick("#weekData", function (e) {
    e.preventDefault();
    loadRecentPatients("week", "#week", "#weeklyReport");
});

listenClick("#dayData", function (e) {
    e.preventDefault();
    loadRecentPatients("day", "#day", "#dailyReport");
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
