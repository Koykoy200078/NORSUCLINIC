<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\AppBaseController;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Specialization;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FrontController extends AppBaseController
{
    /**
     * @return Application|Factory|View
     */
    public function medical(): \Illuminate\View\View
    {
        $doctors = Doctor::with('user', 'specializations')->whereHas('user', function (Builder $query) {
            $query->where('status', User::ACTIVE);
        })->latest()->take(10)->get()->pluck('user.full_name', 'id');
        $sliders = Slider::with('media')->first();
        $aboutExperience = Setting::where('key', 'about_experience')->first();

        return view(
            'fronts.medicals.index',
            compact(
                'doctors',
                'sliders',
                'aboutExperience'
            )
        );
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalAboutUs(): \Illuminate\View\View
    {
        $data = [];
        $data['doctorsCount'] = Doctor::with('user')->get()->where('user.status', true)->count();
        $data['patientsCount'] = Patient::get()->count();
        $data['specializationsCount'] = Specialization::get()->count();
        $setting = Setting::where('key', 'about_us_image')->first();
        $doctors = Doctor::with('user', 'specializations')->whereHas('user', function (Builder $query) {
            $query->where('status', User::ACTIVE);
        })->latest()->take(3)->get();

        return view(
            'fronts.medical_about_us',
            compact('doctors', 'data', 'setting')
        );
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalServices(): \Illuminate\View\View
    {
        $data = [];
        $setting = SettingsService::get();
        $data['doctorsCount'] = Doctor::with('user')->get()->where('user.status', true)->count();
        $data['patientsCount'] = Patient::get()->count();
        $data['specializationsCount'] = Specialization::get()->count();

        return view('fronts.medical_services', compact('setting', 'data'));
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalAppointment(): \Illuminate\View\View
    {
        $appointmentDoctors = Doctor::with('user')->whereHas('user', function (Builder $query) {
            $query->where('status', User::ACTIVE);
        })->get()->pluck('user.full_name', 'id');

        return view('fronts.medical_appointment', compact('appointmentDoctors'));
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalDoctors(): \Illuminate\View\View
    {
        $doctors = Doctor::with('specializations', 'user')->whereHas('user', function (Builder $query) {
            $query->where('status', User::ACTIVE);
        })->latest()->take(9)->get();

        return view('fronts.medical_doctors', compact('doctors'));
    }

    /**
     * @return Application|Factory|View
     */
    public function medicalContact(): \Illuminate\View\View
    {
        return view('fronts.medical_contact');
    }

    /**
     * @return Application|Factory|View
     */
    public function termsCondition(): \Illuminate\View\View
    {
        $termConditions = SettingsService::get();

        return view('fronts.terms_conditions', compact('termConditions'));
    }

    /**
     * @return Application|Factory|View
     */
    public function privacyPolicy(): \Illuminate\View\View
    {
        $privacyPolicy = SettingsService::get();

        return view('fronts.privacy_policy', compact('privacyPolicy'));
    }

    /**
     * @return mixed
     */
    public function changeLanguage(Request $request)
    {
        Session::put('languageName', $request->input('languageName'));

        return $this->sendSuccess(__('messages.flash.language_change'));
    }
}
