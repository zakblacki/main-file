<?php

namespace Workdo\ProductService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Workdo\CMMS\Entities\Location;
use Illuminate\Support\Facades\Validator;
use Workdo\ProductService\Entities\ProductsLogTime;

class ProductsLogTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('product-service::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */

    public function create(Request $request)
    {
        $product_id = $request->product_id;
        return view('product-service::productslogtime_create', compact('product_id'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {

        $objUser            = Auth::user();

        $valid = [
            'date' => 'required',
        ];

        $validator = Validator::make($request->all(), $valid);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $partslogtime = ProductsLogTime::create([
            'product_id' => $request->product_id,
            'hours' => $request->hours,
            'minute' => $request->minute,
            'date' => $request->date,
            'description' => $request->description,
            'created_by' => $objUser->id,
            'company_id' => creatorId(),
            'workspace' => getActiveWorkSpace(),
        ]);

        if ($partslogtime) {
            return redirect()->back()->with(['success' => __('The products log time has been created successfully.'), 'tab-status' => 'log_time']);
        } else {
            return redirect()->back()->with(['error' => __('Something went wrong.'), 'tab-status' => 'log_time']);
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('product-service::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */

    public function edit($id)
    {
        $partslogtime = ProductsLogTime::find($id);
        return view('product-service::productslogtime_edit', compact('partslogtime'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $objUser            = Auth::user();

        $valid = [
            'date' => 'required',
        ];

        $validator = Validator::make($request->all(), $valid);
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }

        $partslogtime['product_id']        = $request->product_id;
        $partslogtime['hours']           = $request->hours;
        $partslogtime['minute']     = $request->minute;
        $partslogtime['date']       = $request->date;
        $partslogtime['description']       = $request->description;

        $partslogtime = ProductsLogTime::where('id', $id)->update($partslogtime);

        return redirect()->back()->with(['success' => __('The products log time details are updated successfully.'), 'tab-status' => 'log_time']);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        $productslogtime = ProductsLogTime::find($id);
        $productslogtime->delete();

        return redirect()->back()->with(['success' => __('The products log time has been deleted.')]);
    }
}
