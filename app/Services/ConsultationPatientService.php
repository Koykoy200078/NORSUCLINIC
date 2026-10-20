<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientType;
use App\Models\Staff;
use App\Models\User;
use App\Models\Vaccination;
use App\Support\ConsultationInput;
use App\Support\PhilippinePhone;
use Illuminate\Support\Facades\Hash;

class ConsultationPatientService
{
    /**
     * Ensure consultation submissions are linked to a patient user.
     * If no user is selected, create a new patient profile from form input.
     */
    public function prepare(array &$data): void
    {
        $data['informant'] = $this->informantLabel(
            $data['informant'] ?? null,
            ConsultationInput::nullableInt($data['year_level_id'] ?? null)
        );

        $userId = ConsultationInput::nullableInt($data['user_id'] ?? null);

        if ($userId !== null) {
            $user = User::query()
                ->with('patient.patientType')
                ->where('id', $userId)
                ->where('type', User::PATIENT)
                ->first();

            if (! $user) {
                throw new \RuntimeException('Selected patient is invalid. Please choose a valid patient from search.');
            }

            $data['informant'] = $this->informantLabel(
                $data['informant'] ?? null,
                ConsultationInput::nullableInt($data['year_level_id'] ?? null),
                $user
            );

            $this->syncPatientProfileFromConsultationData($user, $data);
            $data['user_id'] = $user->id;

            return;
        }

        $matchedUser = $this->findMatchingPatientForConsultationData($data);
        if ($matchedUser) {
            // Auto-linked (not picked by the user): never rewrite the existing patient's
            // identity (name, date of birth, sex) from a walk-in form.
            $this->syncPatientProfileFromConsultationData($matchedUser, $data, false);
            $data['user_id'] = $matchedUser->id;

            return;
        }

        $createdUser = $this->createPatientFromConsultationData($data);
        $data['user_id'] = $createdUser->id;
    }

    /**
     * Try to find an existing patient using consultation identity fields
     * so repeated walk-in entries don't create duplicate patient accounts.
     */
    private function findMatchingPatientForConsultationData(array $data): ?User
    {
        $incomingName = ConsultationInput::nullableString($data['name'] ?? null);
        $dateOfBirth = ConsultationInput::nullableString($data['date_of_birth'] ?? null);
        $gender = $this->normalizeGenderForUser($data['gender'] ?? null);

        if ($dateOfBirth === null || $gender === null) {
            return null;
        }

        $incomingAge = ConsultationInput::nullableInt($data['age'] ?? null);
        if ($incomingAge === null) {
            try {
                $incomingAge = \Carbon\Carbon::parse($dateOfBirth)->age;
            } catch (\Exception $e) {
                return null;
            }
        }

        if ($incomingAge === null) {
            return null;
        }

        $candidates = User::query()
            ->where('type', User::PATIENT)
            ->whereDate('dob', $dateOfBirth)
            ->where('gender', $gender)
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $candidates = $candidates
            ->filter(function (User $candidate) use ($incomingAge) {
                if ($candidate->dob === null) {
                    return false;
                }

                try {
                    return \Carbon\Carbon::parse($candidate->dob)->age === $incomingAge;
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        $nameTokens = $this->tokenizeNameForMatching($incomingName);
        if (! $this->passesNameTokenSafetyThreshold($nameTokens)) {
            return null;
        }

        // Automatic linking requires the SAME name, not a similar one: every name word typed
        // on the form must be one of the patient's name words, and the patient's first and
        // last name must both be present (only the middle name may be omitted). Fuzzy /
        // substring matching used to merge different people ("Ana Cruz" -> "Juliana Cruzado").
        $candidates = $candidates
            ->filter(function (User $candidate) use ($nameTokens) {
                return $this->isExactNameMatchForAutoLink($nameTokens, $candidate);
            })
            ->values();

        if ($candidates->isEmpty()) {
            return null;
        }

        if ($candidates->count() > 1) {
            throw new \RuntimeException(
                'More than one existing patient has this name, date of birth and sex. '
                . 'Please select the correct patient with the patient search box before saving.'
            );
        }

        return $candidates->first();
    }

    private function isExactNameMatchForAutoLink(array $nameTokens, User $candidate): bool
    {
        $incomingTokens = $this->filterMeaningfulNameTokens($nameTokens);
        $candidateTokens = $this->filterMeaningfulNameTokens(
            $this->tokenizeNameForMatching($this->buildFullNameForMatching($candidate))
        );
        $requiredTokens = $this->filterMeaningfulNameTokens(array_merge(
            $this->tokenizeNameForMatching($candidate->first_name),
            $this->tokenizeNameForMatching($candidate->last_name)
        ));

        if (count($incomingTokens) < 2 || empty($requiredTokens)) {
            return false;
        }

        return empty(array_diff($incomingTokens, $candidateTokens))
            && empty(array_diff($requiredTokens, $incomingTokens));
    }

    private function tokenizeNameForMatching(?string $name): array
    {
        $normalizedName = ConsultationInput::nullableString($name);

        if ($normalizedName === null) {
            return [];
        }

        $normalizedName = mb_strtolower(preg_replace('/\s+/u', ' ', $normalizedName));

        // Dots are separators so a middle initial ("M.") becomes a one-letter token, which
        // filterMeaningfulNameTokens() ignores.
        $tokens = preg_split('/[\s,.]+/u', $normalizedName, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_map(function ($token) {
            return trim((string) $token);
        }, $tokens)));
    }

    private function passesNameTokenSafetyThreshold(array $nameTokens): bool
    {
        return count($this->filterMeaningfulNameTokens($nameTokens)) >= 2;
    }

    private function buildFullNameForMatching(User $user): string
    {
        $fullName = trim(implode(' ', array_filter([
            ConsultationInput::nullableString($user->first_name),
            ConsultationInput::nullableString($user->middle_name),
            ConsultationInput::nullableString($user->last_name),
        ])));

        return mb_strtolower(preg_replace('/\s+/u', ' ', $fullName));
    }

    private function filterMeaningfulNameTokens(array $nameTokens): array
    {
        $noiseTokens = ['de', 'del', 'dela', 'la', 'jr', 'sr', 'ii', 'iii', 'iv'];

        $tokens = array_values(array_filter(array_map(function ($token) {
            return mb_strtolower(trim((string) $token));
        }, $nameTokens), function ($token) use ($noiseTokens) {
            if ($token === '') {
                return false;
            }

            if (in_array($token, $noiseTokens, true)) {
                return false;
            }

            return mb_strlen($token) >= 2;
        }));

        return array_values(array_unique($tokens));
    }

    /**
     * Create a patient account/profile from consultation form data.
     */
    private function createPatientFromConsultationData(array $data): User
    {
        [$firstName, $middleName, $lastName] = $this->splitFullName($data['name'] ?? null);

        if ($firstName === '' || $lastName === '') {
            throw new \RuntimeException('Patient name is required to create a patient account.');
        }

        $dateOfBirth = ConsultationInput::nullableString($data['date_of_birth'] ?? null);
        if ($dateOfBirth === null) {
            throw new \RuntimeException('Date of birth is required to create a patient account.');
        }

        $gender = $this->normalizeGenderForUser($data['gender'] ?? null);
        if ($gender === null) {
            throw new \RuntimeException('Gender is required to create a patient account.');
        }

        [$emergencyName, $emergencyNumber, $emergencyRelationship] = $this->extractEmergencyContactParts($data['emergency_contact'] ?? null);

        $user = User::create([
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            ...$this->contactAttributes(ConsultationInput::nullableString($data['patient_contact'] ?? null)),
            'emergency_contact_name' => $emergencyName,
            'emergency_contact_no' => $this->emergencyNumberForUser($emergencyNumber),
            'emergency_relationship' => $emergencyRelationship,
            'dob' => $dateOfBirth,
            'gender' => $gender,
            'status' => true,
            'language' => 'en',
            'type' => User::PATIENT,
            'password' => Hash::make(User::DEFAULT_PASSWORD),
            'email_verified_at' => now()->setTimezone('Asia/Manila')->toDateTimeString(),
            'campus_id' => ConsultationInput::nullableInt($data['campus_id'] ?? null),
            'college_id' => ConsultationInput::nullableInt($data['college_id'] ?? null),
            'course_id' => ConsultationInput::nullableInt($data['course_id'] ?? null),
            'year_level_id' => ConsultationInput::nullableInt($data['year_level_id'] ?? null),
            'department_id' => ConsultationInput::nullableInt($data['department_id'] ?? null),
            'office_id' => ConsultationInput::nullableInt($data['office_id'] ?? null),
            'vaccination_id' => ConsultationInput::nullableInt($data['vaccination_id'] ?? null),
        ]);

        if (! $user->hasRole('patient')) {
            $user->assignRole('patient');
        }

        $this->syncPatientProfileFromConsultationData($user, $data);

        return $user;
    }

    /**
     * Sync consultation form fields into the linked patient profile.
     */
    private function syncPatientProfileFromConsultationData(User $user, array $data, bool $overwriteIdentity = true): void
    {
        [$firstName, $middleName, $lastName] = $this->splitFullName($data['name'] ?? null);
        [$emergencyName, $emergencyNumber, $emergencyRelationship] = $this->extractEmergencyContactParts($data['emergency_contact'] ?? null);
        $hasIncomingName = ConsultationInput::nullableString($data['name'] ?? null) !== null;

        $gender = $this->normalizeGenderForUser($data['gender'] ?? null);

        $userUpdates = [];

        if ($overwriteIdentity && $firstName !== '') {
            $userUpdates['first_name'] = $firstName;
        }

        if ($overwriteIdentity && $hasIncomingName) {
            $userUpdates['middle_name'] = $middleName;
        }

        if ($overwriteIdentity && $lastName !== '') {
            $userUpdates['last_name'] = $lastName;
        }

        if (($contact = ConsultationInput::nullableString($data['patient_contact'] ?? null)) !== null) {
            $userUpdates = array_merge($userUpdates, $this->contactAttributes($contact));
        }

        if ($overwriteIdentity && ($dob = ConsultationInput::nullableString($data['date_of_birth'] ?? null)) !== null) {
            $userUpdates['dob'] = $dob;
        }

        if ($overwriteIdentity && $gender !== null) {
            $userUpdates['gender'] = $gender;
        }

        if ($emergencyName !== null) {
            $userUpdates['emergency_contact_name'] = $emergencyName;
        }

        if ($emergencyNumber !== null) {
            $userUpdates['emergency_contact_no'] = $this->emergencyNumberForUser($emergencyNumber);
        }

        if ($emergencyRelationship !== null) {
            $userUpdates['emergency_relationship'] = $emergencyRelationship;
        }

        if (($campusId = ConsultationInput::nullableInt($data['campus_id'] ?? null)) !== null) {
            $userUpdates['campus_id'] = $campusId;
        }

        if (($collegeId = ConsultationInput::nullableInt($data['college_id'] ?? null)) !== null) {
            $userUpdates['college_id'] = $collegeId;
        }

        if (($courseId = ConsultationInput::nullableInt($data['course_id'] ?? null)) !== null) {
            $userUpdates['course_id'] = $courseId;
        }

        if (($yearLevelId = ConsultationInput::nullableInt($data['year_level_id'] ?? null)) !== null) {
            $userUpdates['year_level_id'] = $yearLevelId;
        }

        if (($departmentId = ConsultationInput::nullableInt($data['department_id'] ?? null)) !== null) {
            $userUpdates['department_id'] = $departmentId;
        }

        if (($officeId = ConsultationInput::nullableInt($data['office_id'] ?? null)) !== null) {
            $userUpdates['office_id'] = $officeId;
        }

        if (($vaccinationId = ConsultationInput::nullableInt($data['vaccination_id'] ?? null)) !== null) {
            $userUpdates['vaccination_id'] = $vaccinationId;
        }

        if (! empty($userUpdates)) {
            $userUpdates['type'] = User::PATIENT;
            $user->update($userUpdates);
        }

        $data['informant'] = $this->informantLabel(
            $data['informant'] ?? null,
            ConsultationInput::nullableInt($data['year_level_id'] ?? null),
            $user
        );

        $patient = $user->patient;
        $patientTypeId = $this->patientTypeId(
            ConsultationInput::nullableInt($data['year_level_id'] ?? null),
            $data['informant'] ?? null,
            $patient === null
        );

        $covidVaccination = null;
        if (isset($vaccinationId) && $vaccinationId !== null) {
            $covidVaccination = Vaccination::query()->whereKey($vaccinationId)->value('vaccination_status');
        }

        // Clinical history on the patient master record (allergies, comorbidities, past
        // admissions, maintenance medication) is only ever ADDED/CHANGED from a consultation,
        // never cleared: a blank field on the form means "not re-entered this visit", not
        // "the patient no longer has this allergy". Clearing it silently removed drug-allergy
        // information that later prescribers rely on.
        $patientPayload = array_filter([
            'allergies' => ConsultationInput::nullableString($data['allergies'] ?? null),
            'comorbidities' => $this->normalizeComorbiditiesValue($data),
            'admissions_surgeries' => ConsultationInput::nullableString($data['admissions_surgeries'] ?? null),
            'maintenance' => ConsultationInput::nullableString($data['maintenance'] ?? null),
        ], function ($value) {
            return $value !== null;
        });

        if ($patientTypeId !== null) {
            $patientPayload['patient_type_id'] = $patientTypeId;
        }

        $resolvedCovidVaccination = $covidVaccination ?? ConsultationInput::nullableString($data['covid_vaccination'] ?? null);
        if ($resolvedCovidVaccination !== null) {
            $patientPayload['covid_vaccination'] = $resolvedCovidVaccination;
        }

        $syncedImmunizationRecord = $this->buildSyncedImmunizationRecord(
            $patient?->immunization_record,
            $resolvedCovidVaccination
        );

        if ($syncedImmunizationRecord !== null) {
            $patientPayload['immunization_record'] = $syncedImmunizationRecord;
        }

        if (! $patient) {
            $patientUniqueId = ConsultationInput::nullableString($data['university_id_number'] ?? null);
            if ($patientUniqueId !== null) {
                $patientUniqueId = strtoupper($patientUniqueId);
            }

            $patient = $user->patient()->create(array_merge($patientPayload, [
                'patient_unique_id' => $patientUniqueId ?: Patient::generatePatientUniqueId(),
            ]));
        } else {
            $patient->update($patientPayload);
        }

        if (($addressText = ConsultationInput::nullableString($data['address'] ?? null)) !== null) {
            if ($patient->address) {
                $patient->address->update(['address1' => $addressText]);
            } else {
                $patient->address()->create(['address1' => $addressText]);
            }
        }

        if (! $user->hasRole('patient')) {
            $user->assignRole('patient');
        }
    }

    public function patientTypeId(?int $yearLevelId, ?string $informant = null, bool $defaultToStudent = false): ?int
    {
        $patientTypeCode = $this->resolvePatientTypeCode($yearLevelId, $informant, $defaultToStudent);

        if ($patientTypeCode === null) {
            return null;
        }

        return PatientType::query()->where('code', $patientTypeCode)->value('id');
    }

    private function resolvePatientTypeCode(?int $yearLevelId, ?string $informant = null, bool $defaultToStudent = false): ?string
    {
        $normalizedInformant = $this->normalizeInformantLabel($informant);

        if ($normalizedInformant !== null) {
            return strtolower($normalizedInformant);
        }

        if ($yearLevelId !== null) {
            if ($yearLevelId === 7) {
                return 'faculty';
            }

            if ($yearLevelId === 8) {
                return 'staff';
            }

            if ($yearLevelId === 9) {
                return 'guest';
            }

            return 'student';
        }

        return $defaultToStudent ? 'student' : null;
    }

    public function informantLabel(?string $informant, ?int $yearLevelId = null, ?User $user = null): string
    {
        $normalizedInformant = $this->normalizeInformantLabel($informant);
        $resolvedYearLevelId = $yearLevelId ?? ConsultationInput::nullableInt($user?->year_level_id ?? null);

        $patientTypeCodeFromProfile = $this->resolvePatientTypeCodeFromUser($user);
        $informantFromProfile = $this->resolveInformantLabelFromPatientTypeCode($patientTypeCodeFromProfile);

        if ($informantFromProfile !== null) {
            if ($normalizedInformant === null) {
                return $informantFromProfile;
            }

            if (
                strtolower($normalizedInformant) === 'student'
                && strtolower($informantFromProfile) !== 'student'
            ) {
                return $informantFromProfile;
            }
        }

        if ($normalizedInformant !== null) {
            return $normalizedInformant;
        }

        $resolvedPatientTypeCode = $this->resolvePatientTypeCode($resolvedYearLevelId, null, true);

        return $this->resolveInformantLabelFromPatientTypeCode($resolvedPatientTypeCode) ?? 'Student';
    }

    private function resolvePatientTypeCodeFromUser(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $user->loadMissing('patient.patientType');

        $patientTypeCode = ConsultationInput::nullableString($user->patient?->patientType?->code);
        return $patientTypeCode !== null ? strtolower($patientTypeCode) : null;
    }

    private function resolveInformantLabelFromPatientTypeCode(?string $patientTypeCode): ?string
    {
        return $this->normalizeInformantLabel($patientTypeCode);
    }

    private function normalizeInformantLabel(?string $informant): ?string
    {
        $normalizedInformant = ConsultationInput::nullableString($informant);

        if ($normalizedInformant === null) {
            return null;
        }

        $informantKey = strtolower(trim(preg_replace('/\s+/', ' ', $normalizedInformant)));

        $informantMap = [
            'student' => 'Student',
            'staff' => 'Staff',
            'employee' => 'Staff',
            'faculty' => 'Faculty',
            'guest' => 'Guest',
            'visitor' => 'Guest',
        ];

        return $informantMap[$informantKey] ?? null;
    }

    private function splitFullName($fullName): array
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $fullName));

        if ($normalized === '') {
            return ['', null, ''];
        }

        $parts = explode(' ', $normalized);

        if (count($parts) === 1) {
            return [$parts[0], null, $parts[0]];
        }

        $firstName = array_shift($parts);
        $lastName = array_pop($parts);
        $middleName = ! empty($parts) ? implode(' ', $parts) : null;

        return [$firstName, $middleName, $lastName];
    }

    /**
     * A Philippine number typed on the consultation form is tidied to "+63 917 123 4567". Anything else
     * (a walk-in without a phone, "N/A") is kept exactly as written: this is a clinical record, not an
     * account form, and the field is read-only for known patients so it could not be corrected there.
     */
    public function normalizeContact(array $data): array
    {
        $contact = ConsultationInput::nullableString($data['patient_contact'] ?? null);

        if ($contact !== null && ($formatted = PhilippinePhone::format($contact)) !== null) {
            $data['patient_contact'] = $formatted;
        }

        return $data;
    }

    /**
     * users.contact / users.country_code for a number entered on the consultation form: canonical digits
     * with country code 63 when it is a Philippine number, otherwise the text as typed.
     *
     * @return array<string, string>
     */
    private function contactAttributes(?string $contact): array
    {
        if ($contact === null) {
            return [];
        }

        $national = PhilippinePhone::national($contact);

        return $national === null
            ? ['contact' => $contact]
            : ['contact' => $national, 'country_code' => PhilippinePhone::COUNTRY_CODE];
    }

    private function emergencyNumberForUser(?string $number): ?string
    {
        return $number === null ? null : (PhilippinePhone::e164($number) ?? $number);
    }

    private function extractEmergencyContactParts($value): array
    {
        $normalized = ConsultationInput::nullableString($value);

        if ($normalized === null) {
            return [null, null, null];
        }

        $name = null;
        $number = null;
        $relationship = null;

        if (preg_match('/^(.+?)(?:\s*\/\s*([^\(]+))?(?:\s*\(([^\)]+)\))?$/', $normalized, $matches)) {
            $name = ConsultationInput::nullableString($matches[1] ?? null);
            $number = ConsultationInput::nullableString($matches[2] ?? null);
            $relationship = ConsultationInput::nullableString($matches[3] ?? null);
        }

        return [$name, $number, $relationship];
    }

    private function normalizeGenderForUser($gender): ?int
    {
        if ($gender === null || $gender === '') {
            return null;
        }

        if (is_numeric($gender)) {
            $normalized = (int) $gender;

            return in_array($normalized, [User::MALE, User::FEMALE], true) ? $normalized : null;
        }

        $normalized = strtolower(trim((string) $gender));

        if (in_array($normalized, ['male', 'm'], true)) {
            return User::MALE;
        }

        if (in_array($normalized, ['female', 'f'], true)) {
            return User::FEMALE;
        }

        return null;
    }

    private function normalizeComorbiditiesValue(array $data): ?string
    {
        $value = $data['comorbidities_custom'] ?? ($data['comorbidities'] ?? null);

        if (is_array($value)) {
            $value = implode(', ', array_filter(array_map(function ($item) {
                return trim((string) $item);
            }, $value)));
        }

        return ConsultationInput::nullableString($value);
    }

    private function buildSyncedImmunizationRecord(?string $currentRecord, ?string $covidVaccination): ?string
    {
        $normalizedRecord = ConsultationInput::nullableString($currentRecord);
        $normalizedVaccination = ConsultationInput::nullableString($covidVaccination);

        if ($normalizedVaccination === null) {
            return $normalizedRecord;
        }

        if (in_array(strtolower($normalizedVaccination), ['unknown vaccination', 'unknown', 'n/a', 'na'], true)) {
            return $normalizedRecord;
        }

        $covidLine = 'COVID-19 Vaccination Status: ' . $normalizedVaccination;

        $existingLines = preg_split('/\R+/', (string) ($normalizedRecord ?? '')) ?: [];
        $existingLines = array_values(array_filter(array_map(function ($line) {
            return trim((string) $line);
        }, $existingLines), function ($line) {
            return $line !== '';
        }));

        $updatedLines = [];
        $hasCovidLine = false;

        foreach ($existingLines as $line) {
            if (preg_match('/^covid(?:-19)?\s+vaccination\s+status\s*:/i', $line)) {
                if (! $hasCovidLine) {
                    $updatedLines[] = $covidLine;
                    $hasCovidLine = true;
                }

                continue;
            }

            $updatedLines[] = $line;
        }

        if (! $hasCovidLine) {
            $updatedLines[] = $covidLine;
        }

        return implode(PHP_EOL, $updatedLines);
    }

    public function ageFromDateOfBirth(?string $dateOfBirth, int $fallbackAge = 0): int
    {
        $normalizedDate = ConsultationInput::nullableString($dateOfBirth);
        $resolvedFallbackAge = max(0, $fallbackAge);

        if ($normalizedDate === null) {
            return $resolvedFallbackAge;
        }

        try {
            return \Carbon\Carbon::parse($normalizedDate)->age;
        } catch (\Exception $e) {
            return $resolvedFallbackAge;
        }
    }
}
