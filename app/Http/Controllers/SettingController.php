<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\City;
use App\Models\Country;
use App\Models\Setting;
use App\Models\Specialization;
use App\Models\State;
use App\Repositories\SettingRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Laracasts\Flash\Flash;
use App\Models\User;
use App\Services\SettingsService;

class SettingController extends AppBaseController
{
    /**
     * @var SettingRepository
     */
    private $settingRepository;

    /**
     * SettingController constructor.
     */
    public function __construct(SettingRepository $SettingRepository)
    {
        $this->settingRepository = $SettingRepository;
    }

    /**
     * @return Application|Factory|View
     */
    public function index(Request $request): \Illuminate\View\View
    {
        $setting = SettingsService::get();
        $sectionName = ($request->get('section') === null) ? 'general' : $request->get('section');
        $states = $cities = [];
        if (isset($setting['country_id'])) {
            $states = getStates($setting['country_id']);
        }
        if (isset($states)) {
            $cities = getCities($states);
        }
        $countries = Country::toBase()->pluck('name', 'id');
        $specialties = Specialization::orderBy('name', 'asc')->pluck('name', 'id');

        return view(
            "setting.$sectionName",
            compact('sectionName', 'setting', 'countries', 'specialties', 'states', 'cities')
        );
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $language = $request->language;
        if (!empty($language)) {
            Setting::where('key', 'language')->update([
                'value' => $language,
            ]);
            // One UPDATE instead of loading and saving every non-admin user one by one.
            User::whereNot('type', User::ADMIN)->update(['language' => $language]);
            session()->forget('languageName');
        }

        $id = Auth::id();

        $this->settingRepository->update($request->all(), $id);
        Flash::success(__('messages.flash.setting_update'));

        return Redirect::back();
    }

    /**
     * @return mixed
     */
    public function getStates(Request $request)
    {
        $countryId = $request->get('settingCountryId');
        $data['state_id'] = getSettingValue('state_id');
        $data['states'] = State::where('country_id', $countryId)->toBase()->pluck('name', 'id')->toArray();

        return $this->sendResponse($data, __('messages.flash.states_retrieve'));
    }

    /**
     * @return mixed
     */
    public function getCities(Request $request)
    {
        $state_id = $request->get('stateId');
        $data['city_id'] = getSettingValue('city_id');
        $data['cities'] = City::where('state_id', $state_id)->toBase()->pluck('name', 'id')->toArray();

        return $this->sendResponse($data, __('messages.flash.cities_retrieve'));
    }
}
