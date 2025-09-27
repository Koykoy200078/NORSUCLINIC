document.addEventListener("DOMContentLoaded", loadAppointmentCalendar);

let popover;
let popoverState = false;
let appointmentStatusId = null;
let calendar;
let data = {
    id: "",
    uId: "",
    eventName: "",
    patientName: "",
    eventDescription: "",
    eventStatus: "",
    startDate: "",
    endDate: "",
    amount: 0,
    service: "",
    doctorName: "",
};

// View event variables
let viewEventName,
    viewEventDescription,
    viewEventStatus,
    viewStartDate,
    viewPatientName,
    viewEndDate,
    viewModal,
    viewEditButton,
    viewDeleteButton,
    viewService,
    viewUId,
    viewAmount;

function loadAppointmentCalendar() {
    initCalendarApp();
    init();
}

const initCalendarApp = function () {
    if (!$("#adminAppointmentCalendar").length) {
        return;
    }
    if (usersRole == "patient") {
        return;
    }
    let calendarEl = document.getElementById("adminAppointmentCalendar");
    let lang = $(".currentLanguage").val();

    calendar = new FullCalendar.Calendar(calendarEl, {
        locale: lang,
        themeSystem: "bootstrap5",
        height: 750,
        buttonText: {
            today: Lang.get("js.today"),
            day: Lang.get("js.day"),
            month: Lang.get("js.month"),
        },
        headerToolbar: {
            left: "title",
            center: "prev,next today",
            right: "dayGridDay,dayGridMonth",
        },

        initialDate: new Date(),
        timeZone: "Asia/Manila",
        dayMaxEvents: true,
        events: function (info, successCallback, failureCallback) {
            // Determine the correct route based on user role
            let calendarRoute;
            switch(usersRole) {
                case 'staff':
                    calendarRoute = route('staff.appointments.calendar');
                    break;
                case 'doctor':
                    calendarRoute = route('doctors.appointments.calendar');
                    break;
                case 'patient':
                    calendarRoute = route('patient.appointments.calendar');
                    break;
                default:
                    calendarRoute = route('appointments.calendar'); // admin route
                    break;
            }
            
            $.ajax({
                url: calendarRoute,
                type: "GET",
                data: info,
                success: function (result) {
                    if (result.success) {
                        successCallback(result.data);
                    }
                },
                error: function (result) {
                    let errorMessage = 'An error occurred while loading calendar data.';
                    if (result.responseJSON && result.responseJSON.message) {
                        errorMessage = result.responseJSON.message;
                    } else if (result.responseText) {
                        errorMessage = result.responseText;
                    }
                    displayErrorMessage(errorMessage);
                    failureCallback();
                },
            });
        },
        // MouseEnter event --- more info: https://fullcalendar.io/docs/eventMouseEnter
        eventMouseEnter: function (arg) {
            formatArgs({
                id: arg.event.id,
                title: arg.event.title,
                startStr: arg.event.startStr,
                endStr: arg.event.endStr,
                patient: arg.event.extendedProps.patient,
                status: arg.event.extendedProps.status,
                amount: arg.event.extendedProps.amount,
                uId: arg.event.extendedProps.uId,
                service: arg.event.extendedProps.service,
                doctorName: arg.event.extendedProps.doctorName,
            });

            // Show popover preview
            initPopovers(arg.el);
        },
        eventMouseLeave: function () {
            hidePopovers();
        },
        // Click event --- more info: https://fullcalendar.io/docs/eventClick
        eventClick: function (arg) {
            hidePopovers();
            appointmentStatusId = arg.event.id;
            formatArgs({
                id: arg.event.id,
                title: arg.event.title,
                startStr: arg.event.startStr,
                endStr: arg.event.endStr,
                patient: arg.event.extendedProps.patient,
                status: arg.event.extendedProps.status,
                amount: arg.event.extendedProps.amount,
                uId: arg.event.extendedProps.uId,
                service: arg.event.extendedProps.service,
                doctorName: arg.event.extendedProps.doctorName,
            });
            handleViewEvent();
        },
    });

    calendar.render();
};

const init = () => {
    if (!$("#eventModal").length) {
        return;
    }
    const viewElement = document.getElementById("eventModal");
    viewModal = new bootstrap.Modal(viewElement);
    viewEventName = viewElement.querySelector('[data-calendar="event_name"]');
    viewPatientName = viewElement.querySelector(
        '[data-calendar="event_patient_name"]'
    );
    viewEventDescription = viewElement.querySelector(
        '[data-calendar="event_description"]'
    );
    viewEventStatus = viewElement.querySelector(
        '[data-calendar="event_status"]'
    );
    viewAmount = viewElement.querySelector('[data-calendar="event_amount"]');
    viewUId = viewElement.querySelector('[data-calendar="event_uId"]');
    viewService = viewElement.querySelector('[data-calendar="event_service"]');
    viewStartDate = viewElement.querySelector(
        '[data-calendar="event_start_date"]'
    );
    viewEndDate = viewElement.querySelector('[data-calendar="event_end_date"]');
};

// Format FullCalendar responses
const formatArgs = (res) => {
    data.id = res.id;
    data.eventName = res.title;
    data.patientName = res.patient;
    data.eventDescription = res.description;
    data.eventStatus = res.status;
    data.startDate = res.startStr;
    data.endDate = res.endStr;
    data.amount = res.amount;
    data.uId = res.uId;
    data.service = res.service;
    data.doctorName = res.doctorName;
};

// Initialize popovers --- more info: https://getbootstrap.com/docs/4.0/components/popovers/
const initPopovers = (element) => {
    hidePopovers();

    // Generate popover content
    const startDate = data.allDay
        ? moment(data.startDate).format("Do MMM, YYYY")
        : moment(data.startDate).format("Do MMM, YYYY - h:mm a");
    const endDate = data.allDay
        ? moment(data.endDate).format("Do MMM, YYYY")
        : moment(data.endDate).format("Do MMM, YYYY - h:mm a");
    const popoverHtml =
        '<div class="fw-bolder mb-2"><b>Doctor</b>: ' +
        data.doctorName +
        '<div class="fw-bolder mb-2"><b>Patient</b>: ' +
        data.patientName +
        '</div><div class="fs-7"><span class="fw-bold">Start:</span> ' +
        startDate +
        '</div><div class="fs-7 mb-4"><span class="fw-bold">End:</span> ' +
        endDate +
        "</div>";

    // Popover options
    let options = {
        container: "body",
        trigger: "manual",
        boundary: "window",
        placement: "auto",
        dismiss: true,
        html: true,
        title: "Appointment Details",
        content: popoverHtml,
    };
};

// Hide active popovers
const hidePopovers = () => {
    if (popoverState) {
        popover.dispose();
        popoverState = false;
    }
};

// Handle view event
const handleViewEvent = () => {
    $(".fc-popover").addClass("hide");
    viewModal.show();

    // Detect all day event
    let eventNameMod;
    let startDateMod;
    let endDateMod;
    let book = $("#bookCalenderConst").val();
    let accepted = $("#acceptedCalenderConst").val();
    let finished = $("#finishedOutCalenderConst").val();
    let cancel = $("#cancelCalenderConst").val();

    eventNameMod = "";

    startDateMod = moment(data.startDate).utc().format("DD MMM, YYYY - h:mm A");
    endDateMod = moment(data.endDate).utc().format("DD MMM, YYYY - h:mm A");
    viewEndDate.innerText = ": " + endDateMod;
    viewStartDate.innerText = ": " + startDateMod;

    // Populate view data
    viewEventName.innerText = Lang.get("js.doctor") + ": " + data.doctorName;
    viewPatientName.innerText =
        Lang.get("js.patient") + ": " + data.patientName;
    $(viewEventStatus).empty();
    $(viewEventStatus).append(`
<option class="booked" disabled value="${book}" ${
        data.eventStatus == book ? "selected" : ""
    }>${Lang.get("js.booked")}</option>
<option value="${accepted}" ${data.eventStatus == accepted ? "selected" : ""} ${
        data.eventStatus == accepted ? "selected" : ""
    }
    ${
        data.eventStatus == cancel || data.eventStatus == finished
            ? "disabled"
            : ""
    }>${Lang.get("js.check_in")}</option>
<option value="${finished}" ${data.eventStatus == finished ? "selected" : ""}
    ${
        data.eventStatus == cancel || data.eventStatus == book ? "disabled" : ""
    }>${Lang.get("js.check_out")}</option>
<option value="${cancel}" ${data.eventStatus == cancel ? "selected" : ""} ${
        data.eventStatus == accepted ? "disabled" : ""
    }
   ${data.eventStatus == finished ? "disabled" : ""}>${Lang.get(
        "js.cancelled"
    )}</option>
`);
    $(viewEventStatus).val(data.eventStatus).trigger("change");
    viewAmount.innerText = addCommas(data.amount);
    viewUId.innerText = data.uId;
    viewService.innerText = data.service;
};

listenChange("#changeAppointmentStatus", function () {
    if (!$(this).val()) {
        return false;
    }
    let appointmentStatus = $(this).val();
    let appointmentId = appointmentStatusId;
    if (parseInt(appointmentStatus) === data.eventStatus) {
        return false;
    }
    
    // Determine the correct change-status route based on user role
    let changeStatusRoute;
    switch(usersRole) {
        case 'staff':
            changeStatusRoute = route('staff.change-status', appointmentId);
            break;
        case 'doctor':
            changeStatusRoute = route('doctors.change-status', appointmentId);
            break;
        default:
            changeStatusRoute = route('change-status', appointmentId); // admin route
            break;
    }
    
    $.ajax({
        url: changeStatusRoute,
        type: "POST",
        data: {
            appointmentId: appointmentId,
            appointmentStatus: appointmentStatus,
        },
        success: function (result) {
            displaySuccessMessage(result.message);
            $("#eventModal").modal("hide");
            calendar.refetchEvents();
        },
        error: function (result) {
            let errorMessage = 'An error occurred while changing appointment status.';
            if (result.responseJSON && result.responseJSON.message) {
                errorMessage = result.responseJSON.message;
            }
            displayErrorMessage(errorMessage);
        }
    });
});
