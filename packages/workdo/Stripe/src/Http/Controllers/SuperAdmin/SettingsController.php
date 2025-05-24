<?php
// This file use for handle super admin setting page

namespace Workdo\Stripe\Http\Controllers\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($settings)
    {
        return view('stripe::super-admin.settings.index',compact('settings'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }
}
