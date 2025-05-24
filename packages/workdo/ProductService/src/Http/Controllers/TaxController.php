<?php

namespace Workdo\ProductService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\ProductService\Entities\Tax;
use Workdo\ProductService\Events\CreateTax;
use Workdo\ProductService\Events\DestroyTax;
use Workdo\ProductService\Events\UpdateTax;

class TaxController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if(Auth::user()->isAbleTo('tax create'))
        {
            return view('product-service::taxes.create');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('tax create'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'name' => 'required|max:20',
                                'rate' => 'required|numeric',
                            ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $tax             = new Tax();
            $tax->name       = $request->name;
            $tax->rate       = $request->rate;
            $tax->created_by = creatorId();
            $tax->workspace_id = getActiveWorkSpace();
            $tax->save();
            event(new CreateTax($request,$tax));
            return redirect()->route('category.index')->with('success', __('The tax rate has been created successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return redirect()->route('category.index')->with('error', __('Permission denied.'));
        return view('product-service::taxes.show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit(Tax $tax)
    {
        if(Auth::user()->isAbleTo('tax edit'))
        {
            return view('product-service::taxes.edit', compact('tax'));
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, Tax $tax)
    {
        if(Auth::user()->isAbleTo('tax edit'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                'name' => 'required|max:20',
                                'rate' => 'required|numeric',
                            ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $tax->name = $request->name;
            $tax->rate = $request->rate;
            $tax->save();
            event(new UpdateTax($request,$tax));
            return redirect()->route('category.index')->with('success', __('The tax rate details are updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Tax $tax)
    {
        if(Auth::user()->isAbleTo('tax delete'))
        {
            event(new DestroyTax($tax));
            $tax->delete();

            return redirect()->route('category.index')->with('success', __('The tax rate has been deleted.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }
}
