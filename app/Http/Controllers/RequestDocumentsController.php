<?php

namespace App\Http\Controllers;

use App\Models\RequestDocuments;
use App\Repositories\RequestRepository;
use Illuminate\Http\Request;

class RequestDocumentsController extends Controller
{
    private $requestRepository;

    public function __construct(RequestRepository $requestRepo)
    {
        $this->requestRepository = $requestRepo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('requests.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestDocuments $requestDocuments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RequestDocuments $requestDocuments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestDocuments $requestDocuments)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestDocuments $requestDocuments)
    {
        //
    }
}
