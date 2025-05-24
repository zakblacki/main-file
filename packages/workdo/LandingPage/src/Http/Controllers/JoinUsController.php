<?php

namespace Workdo\LandingPage\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\LandingPage\Entities\LandingPageSetting;
use Workdo\LandingPage\Entities\JoinUs;


class JoinUsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index()
    {

        if(\Auth::user())
        {
            if(\Auth::user()->isAbleTo('landingpage manage')){

                $join_us = JoinUs::get();
                $settings = LandingPageSetting::settings();

                return view('landingpage::landingpage.newsletter.index', compact('join_us','settings'));
            }else{

                return redirect()->back()->with('error',__('Permission Denied!'));
            }
        }
        else
        {
            return redirect()->route('login');
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('landingpage::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $data['is_newsletter_enabled']  = $request->is_newsletter_enabled;
        $data['joinus_heading']         = $request->joinus_heading;
        $data['joinus_description']     = $request->joinus_description;

        foreach($data as $key => $value){
            LandingPageSetting::updateOrCreate(['name' =>  $key],['value' => $value]);
        }

        return redirect()->back()->with(['success'=> 'Setting update successfully']);


    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->back();
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('landingpage::landingpage.joinus');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $join = JoinUs::find($id);
        $join->delete();

        return redirect()->back()->with(['success'=> 'The Join Us email has been deleted']);
    }


    public function joinUsUserStore(Request $request){

        $validator = \Validator::make(
            $request->all(),
            [
                'email' => 'required|email|unique:join_us',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $join = new JoinUs;
        $join->email = $request->email;
        $join->save();

        return redirect()->back()->with(['success'=> 'You are joined with our community']);
    }
}
