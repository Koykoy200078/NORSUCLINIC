<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\UpdateFrontCmsRequest;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Laracasts\Flash\Flash;

class CMSController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(): \Illuminate\View\View
    {
        $cmsData = Setting::pluck('value', 'key')->toArray();

        if (isset($cmsData['about_image_1'])) {
            $cmsData['about_image_1'] = normalizeLocalUrl($cmsData['about_image_1']);
        }

        if (isset($cmsData['about_image_2'])) {
            $cmsData['about_image_2'] = normalizeLocalUrl($cmsData['about_image_2']);
        }

        if (isset($cmsData['about_image_3'])) {
            $cmsData['about_image_3'] = normalizeLocalUrl($cmsData['about_image_3']);
        }

        return view('fronts.cms.cms', compact('cmsData'));
    }

    /**
     * @return Application|RedirectResponse|Redirector
     */
    public function update(UpdateFrontCmsRequest $request): RedirectResponse
    {
        $data = [];
        $input = $request->all();
        // Purify the rich-text HTML at write time: these values are rendered raw ({!! !!}) on
        // PUBLIC terms/privacy pages, so unsanitized input is a stored-XSS sink. E-SEC-4.
        // (This is the only place HTML input is accepted; everything else is plain text and is
        // escaped on output by Blade {{ }}.)
        $data['terms_conditions'] = \Mews\Purifier\Facades\Purifier::clean((string) json_decode($input['terms_conditions']));
        $data['privacy_policy'] = \Mews\Purifier\Facades\Purifier::clean((string) json_decode($input['privacy_policy']));
        $data['about_title'] = $input['about_title'];
        $data['about_short_description'] = $input['about_short_description'];
        $data['about_experience'] = $input['about_experience'];

        // The About texts and images are saved together: a failing upload must not leave a half-edited page.
        DB::transaction(function () use ($data, $input) {
            foreach (['about_image_1', 'about_image_2', 'about_image_3'] as $key) {
                if (isset($input[$key])) {
                    $setting = Setting::where('key', $key)->first();
                    $setting->clearMediaCollection(Setting::IMAGE);
                    $media = $setting->addMedia($input[$key])->toMediaCollection(Setting::IMAGE, config('app.media_disc'));
                    $setting->update(['value' => $media->getUrl()]);
                }
            }

            foreach ($data as $key => $value) {

                /** @var Setting $setting */
                $setting = Setting::where('key', $key)->first();
                if (! $setting) {
                    continue;
                }

                $setting->update(['value' => $value]);
            }
        });

        // The public About page reads these through the settings cache (one hour): show the new text at once.
        SettingsService::clearCache();

        Flash::success(__('messages.flash.cms_update'));

        return redirect(route('cms.index'));
    }
}
