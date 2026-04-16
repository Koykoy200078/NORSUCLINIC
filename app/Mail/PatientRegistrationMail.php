<?php

namespace App\Mail;

use App\Models\Patient;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PatientRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $patient;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Patient $patient)
    {
        $this->user = $user;
        $this->patient = $patient;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        $clinicName = getSettingValue('clinic_name') ?: 'NORSU Clinic';

        // Get featured slider or first available slider
        $slider = Slider::where('is_default', true)->first() ?: Slider::first();
        $patientIdentifier = $this->user->university_id_number ?: $this->patient->patient_unique_id;

        return $this->subject("Welcome to {$clinicName} - Registration Successful!")
            ->view('emails.patient-registration')
            ->with([
                'patientName' => $this->user->first_name . ' ' . $this->user->last_name,
                'patientId' => $patientIdentifier,
                'email' => $this->user->email,
                'registrationDate' => now()->setTimezone('Asia/Manila')->format('F j, Y g:i A'),
                'slider' => $slider,
            ]);
    }
}
