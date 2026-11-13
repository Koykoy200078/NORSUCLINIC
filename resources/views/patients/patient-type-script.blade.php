{{-- Shows the fields of the chosen patient type (student / faculty / staff / guest) and fills the year level list. Shared by the
     patient form ($idSuffix '') and the patient's profile page ($idSuffix 'Profile'; it has no university ID or heading, so
     those lines find nothing there). Included inside a DOMContentLoaded handler. Re-audit 2026-11-13, DRY. --}}
const patientTypeSelect = document.getElementById('patientTypeSelect{{ $idSuffix }}');
const campusFieldContainer = document.getElementById('campusFieldContainer{{ $idSuffix }}');
const collegeFieldContainer = document.getElementById('collegeFieldContainer{{ $idSuffix }}');
const courseFieldContainer = document.getElementById('courseFieldContainer{{ $idSuffix }}');
const departmentFieldContainer = document.getElementById('departmentFieldContainer{{ $idSuffix }}');
const officeFieldContainer = document.getElementById('officeFieldContainer{{ $idSuffix }}');
const yearLevelFieldContainer = document.getElementById('yearLevelFieldContainer{{ $idSuffix }}');
const universityIdFieldContainer = document.getElementById('universityIdFieldContainer{{ $idSuffix }}');
const patientInformationHeadingContainer = document.getElementById('patientInformationHeadingContainer{{ $idSuffix }}');
const universityIdInput = document.querySelector('#universityIdFieldContainer{{ $idSuffix }} input[name="university_id_number"]');

const campusSelect = document.getElementById('campusSelect{{ $idSuffix }}');
const collegeSelect = document.getElementById('collegeSelect{{ $idSuffix }}');
const courseSelect = document.getElementById('courseSelect{{ $idSuffix }}');
const departmentSelect = document.getElementById('departmentSelect{{ $idSuffix }}');
const officeSelect = document.getElementById('officeSelect{{ $idSuffix }}');
const yearLevelSelect = document.getElementById('yearLevelSelect{{ $idSuffix }}');

const allYearLevels = JSON.parse(document.getElementById('allYearLevels{{ $idSuffix }}').value || '{}');
const patientTypeLookup = JSON.parse(document.getElementById('patientTypeLookup{{ $idSuffix }}').value || '{}');

const studentYearLevels = {};
let facultyYearLevelId = null;
let staffYearLevelId = null;
let guestYearLevelId = null;

Object.entries(allYearLevels).forEach(([id, name]) => {
    const normalized = String(name).trim().toLowerCase();
    if (normalized.includes('faculty')) {
        facultyYearLevelId = id;
    } else if (normalized.includes('staff')) {
        staffYearLevelId = id;
    } else if (normalized.includes('guest')) {
        guestYearLevelId = id;
    } else {
        studentYearLevels[id] = name;
    }
});

function setVisible(container, visible) {
    if (!container) {
        return;
    }

    container.style.display = visible ? 'block' : 'none';
}

function clearSelectValue(selectElement) {
    if (!selectElement) {
        return;
    }

    $(selectElement).val(null).trigger('change');
}

function setSingleYearLevel(yearLevelId) {
    if (!yearLevelSelect) {
        return;
    }

    $(yearLevelSelect).empty();

    if (yearLevelId && allYearLevels[yearLevelId]) {
        const option = new Option(allYearLevels[yearLevelId], yearLevelId, true, true);
        $(yearLevelSelect).append(option);
        $(yearLevelSelect).val(yearLevelId).trigger('change');
        return;
    }

    $(yearLevelSelect).append(new Option('{{ __("messages.student.select_year_level") }}', '', true, true));
    $(yearLevelSelect).val('').trigger('change');
}

function setStudentYearLevels(currentValue) {
    if (!yearLevelSelect) {
        return;
    }

    const normalizedCurrentValue = Array.isArray(currentValue) ? currentValue[0] : currentValue;
    $(yearLevelSelect).empty();
    $(yearLevelSelect).append(new Option('{{ __("messages.student.select_year_level") }}', '', false, false));

    Object.entries(studentYearLevels).forEach(([value, text]) => {
        const option = new Option(text, value, false, value == normalizedCurrentValue);
        $(yearLevelSelect).append(option);
    });

    if (normalizedCurrentValue && studentYearLevels[normalizedCurrentValue]) {
        $(yearLevelSelect).val(normalizedCurrentValue).trigger('change');
        return;
    }

    $(yearLevelSelect).val('').trigger('change');
}

function updateFieldsDisplay() {
    const selectedPatientTypeId = String($(patientTypeSelect).val() || '');
    const selectedPatientType = String(patientTypeLookup[selectedPatientTypeId] || '').toLowerCase();

    const isStudent = selectedPatientType === 'student';
    const isFaculty = selectedPatientType === 'faculty';
    const isStaff = selectedPatientType === 'staff';
    const isGuest = selectedPatientType === 'guest';

    setVisible(campusFieldContainer, isStudent);
    setVisible(collegeFieldContainer, isStudent || isFaculty);
    setVisible(courseFieldContainer, isStudent);
    setVisible(departmentFieldContainer, isFaculty);
    setVisible(officeFieldContainer, isStaff);
    setVisible(yearLevelFieldContainer, isStudent);
    setVisible(universityIdFieldContainer, !isGuest);
    setVisible(patientInformationHeadingContainer, selectedPatientTypeId !== '' && !isGuest);

    if (universityIdInput) {
        if (isGuest) {
            universityIdInput.removeAttribute('required');
            universityIdInput.value = '';
        } else {
            universityIdInput.setAttribute('required', 'required');
        }
    }

    if (!isStudent) {
        clearSelectValue(campusSelect);
        clearSelectValue(courseSelect);
    }

    if (!(isStudent || isFaculty)) {
        clearSelectValue(collegeSelect);
    }

    if (!isFaculty) {
        clearSelectValue(departmentSelect);
    }

    if (!isStaff) {
        clearSelectValue(officeSelect);
    }

    const currentYearLevel = $(yearLevelSelect).val();

    if (isStudent) {
        setStudentYearLevels(currentYearLevel);
    } else if (isFaculty) {
        setSingleYearLevel(facultyYearLevelId);
    } else if (isStaff) {
        setSingleYearLevel(staffYearLevelId);
    } else if (isGuest) {
        setSingleYearLevel(guestYearLevelId);
    } else {
        setSingleYearLevel(null);
    }
}

window.updateFieldsDisplay = updateFieldsDisplay;
updateFieldsDisplay();
$(patientTypeSelect).off('change.patientType').on('change.patientType', updateFieldsDisplay);
