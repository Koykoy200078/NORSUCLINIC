<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGenericRequest;
use App\Http\Requests\UpdateGenericRequest;
use App\Models\Generic;
use App\Models\Medicine;
use App\Repositories\GenericRepository;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class GenericController extends AppBaseController
{
    /** @var GenericRepository */
    private $genericRepository;

    public function __construct(GenericRepository $genericRepo)
    {
        $this->genericRepository = $genericRepo;
    }

    /**
     * Get the appropriate generic index route based on user role
     */
    private function getGenericIndexRoute(): string
    {
        if (isRole('clinic_admin')) {
            return route('generics.index');
        } elseif (isRole('staff')) {
            return route('staff.generics.index');
        } elseif (isRole('doctor')) {
            return route('doctors.generics.index');
        }

        return route('generics.index');
    }

    /**
     * Display a listing of the Generic.
     *
     * @param  Request  $request
     * @return Factory|View
     *
     * @throws Exception
     */
    public function index(): View
    {
        return view('generics.index');
    }

    /**
     * @return Application|Factory|View
     */
    public function create(): View
    {
        return view('generics.create');
    }

    /**
     * Store a newly created Generic in storage.
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function store(CreateGenericRequest $request): RedirectResponse
    {
        $input = $request->all();
        $this->genericRepository->create($input);
        Flash::success(__('messages.medicine_generics') . ' ' . __('messages.medicine.saved_successfully'));

        return redirect($this->getGenericIndexRoute());
    }

    /**
     * @return Factory|View
     */
    public function show(Generic $generic): View
    {
        $medicines = $generic->medicines;

        return view('generics.show', compact('medicines', 'generic'));
    }

    /**
     * Show the form for editing the specified Generic.
     *
     * @return Application|Factory|View
     */
    public function edit(Generic $generic): View
    {
        return view('generics.edit', compact('generic'));
    }

    /**
     * Update the specified Generic in storage.
     *
     * @return Application|RedirectResponse|Redirector
     */
    public function update(Generic $generic, UpdateGenericRequest $request): RedirectResponse
    {
        $input = $request->all();
        $this->genericRepository->update($input, $generic->id);
        Flash::success(__('messages.medicine_generics') . ' ' . __('messages.medicine.updated_successfully'));

        return redirect($this->getGenericIndexRoute());
    }

    /**
     * Remove the specified Generic from storage.
     *
     *
     * @throws Exception
     */
    public function destroy(Generic $generic): JsonResponse
    {
        // Null out generic_id on any medicines using this generic before deleting
        Medicine::where('generic_id', $generic->id)->update(['generic_id' => null]);

        $generic->delete();

        return $this->sendSuccess(__('messages.medicine_generics') . ' ' . __('messages.medicine.deleted_successfully'));
    }
}
