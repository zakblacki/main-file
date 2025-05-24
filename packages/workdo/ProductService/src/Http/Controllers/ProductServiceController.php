<?php

namespace Workdo\ProductService\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\ProposalProduct;
use Illuminate\Support\Facades\Auth;
use Workdo\ProductService\Entities\Category;
use Workdo\ProductService\Entities\ProductService;
use Workdo\ProductService\Entities\Tax;
use Workdo\ProductService\Entities\Unit;
use App\Events\DeleteProductService;
use App\Models\User;
use App\Models\Purchase;
use App\Models\InvoiceProduct;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Workdo\Pos\Entities\PosProduct;
use Workdo\ProductService\Entities\ProductsLogTime;
use Illuminate\Support\Facades\DB;
use Workdo\ProductService\Events\CreateProduct;
use Workdo\ProductService\Events\DestroyProduct;
use Workdo\ProductService\Events\UpdateProduct;
use Workdo\CMMS\Entities\Workorder;
use Workdo\CMMS\Entities\Component;
use Workdo\CMMS\Entities\Pms;
use Workdo\Bookings\Entities\BookingsDuration;
use Workdo\ConsignmentManagement\Entities\ConsignmentWeight;
use Workdo\RestaurantMenu\Entities\RestaurantSystemSet;
use Workdo\RestaurantMenu\Entities\RestaurantVarient;
use Workdo\Facilities\Entities\FacilitiesSpace;
use Workdo\Facilities\Entities\FacilitiesService;
use Workdo\JewelleryStoreManagement\Entities\JewelleryItem;
use Workdo\ProductService\DataTables\ProductServiceDataTable;
use Workdo\OpticalAndEyeCareCenter\Entities\OpticalEyeCareItems;
class ProductServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(ProductServiceDataTable $dataTable)
    {
        if(Auth::user()->isAbleTo('product&service manage'))
        {
            $category = Category::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->where('type', '=', 0)->get()->pluck('name', 'id');
            $product_type = ProductService::$product_type;
            if(module_is_active('RentalManagement')) {
                $product_type['rent'] = 'Rent';
            }
            if(module_is_active('MusicInstitute')) {
                $product_type['music institute'] = 'Music Institute';
            }
            if(module_is_active('Bookings')) {
                $product_type['bookings'] = 'Bookings';
            }
            if(module_is_active('Facilities')) {
                $product_type['facilities'] = 'Facilities';
            }
            if(module_is_active('Fleet')) {
                $product_type['fleet'] = 'Fleet';
            }
            if (module_is_active('ConsignmentManagement')) {
                $product_type['consignment'] = 'Consignment';
            }
            if (module_is_active('OpticalAndEyeCareCenter')) {
                $product_type['optical eyecare'] = 'Optical & Eye Care';
            }
            if (module_is_active(('JewelleryStoreManagement'))) {
                $product_type['jewellery store'] = 'Jewellery Store';
            }
            return $dataTable->render('product-service::index',compact('category','product_type'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        if(Auth::user()->isAbleTo('product&service create'))
        {
            $product_type = ProductService::$product_type;
            if(module_is_active('RentalManagement')) {
                $product_type['rent'] = 'Rent';
            }
            if(module_is_active('MusicInstitute')) {
                $product_type['music institute'] = 'Music Institute';
            }
            if(module_is_active('RestaurantMenu')) {
                $product_type['restaurants'] = 'Restaurants';
            }
            if(module_is_active('Bookings')) {
                $product_type['bookings'] = 'Bookings';
            }
            if(module_is_active('Facilities')) {
                $product_type['facilities'] = 'Facilities';
            }
            if(module_is_active('Fleet')) {
                $product_type['fleet'] = 'Fleet';
            }
            if (module_is_active('ConsignmentManagement')) {
                $product_type['consignment'] = 'Consignment';
            }
            if (module_is_active('OpticalAndEyeCareCenter')) {
                $product_type['optical eyecare'] = 'Optical & Eye Care';
            }
            if (module_is_active(('JewelleryStoreManagement'))) {
                $product_type['jewellery store'] = 'Jewellery Store';
            }
            return view('product-service::create', compact('product_type'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function ProductSectionGet(Request $request)
    {
        $type = $request->item_type;
        $action = $request->action;
        $productService =[];
        if ($action == 'edit') {
            $productService = ProductService::find($request->item_id);
        }
        if(module_is_active('CustomField')){
            $customFields =  \Workdo\CustomField\Entities\CustomField::where('workspace_id',getActiveWorkSpace())->where('module', '=', 'ProductService')->where('sub_module','product & service')->get();
        }else{
            $customFields = null;
        }

        $category     = Category::where('created_by', '=',creatorId())->where('workspace_id', '=', getActiveWorkSpace())->where('type', '=', 0)->get()->pluck('name', 'id');
        $unit         = Unit::where('created_by', '=',creatorId())->where('workspace_id', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
        $tax          = Tax::where('created_by', '=',creatorId())->where('workspace_id', '=', getActiveWorkSpace())->get()->pluck('name', 'id');

        if($type == 'product' || $type == 'parts')
        {
            $components_id = $request->components_id;
            $pms_id = $request->pms_id;
            $supplier_id = $request->supplier_id;
            $workorder_id = $request->workorder_id;

            $warehouse = Warehouse::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $warehouse->prepend('Select Warehouse', '');
            $returnHTML = view('product-service::product_section', compact('category', 'type', 'unit', 'tax', 'warehouse','customFields','components_id','pms_id','supplier_id','workorder_id','productService','action'))->render();
            $response = [
                'is_success' => true,
                'message' => '',
                'html' => $returnHTML,
            ];
            return response()->json($response);
        }
        elseif($type == 'service' || $type == 'rent' || $type == 'music institute' || $type == 'fleet')
        {
            $returnHTML = view('product-service::service_section', compact('category', 'type', 'unit', 'tax','customFields','productService','action'))->render();
            $response = [
                'is_success' => true,
                'message' => '',
                'html' => $returnHTML,
            ];
            return response()->json($response);
        }
        elseif($type == 'restaurants' && module_is_active('RestaurantMenu'))
        {
            $varient = [];
            if(!empty($productService))
            {
                $varient = RestaurantVarient::where('item_id',$productService->id)->first();
            }
            $itemAttribute = RestaurantSystemSet::where('status','active')->where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $category     = Category::join('restaurant_categories','restaurant_categories.category_id','categories.id')->where('categories.created_by', '=',creatorId())->where('categories.workspace_id', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
            $returnHTML = view('restaurant-menu::item.section', compact('category', 'type', 'unit', 'tax','customFields','itemAttribute','productService','action','varient'))->render();
            $response = [
                'is_success' => true,
                'message' => '',
                'html' => $returnHTML,
            ];
            return response()->json($response);
        }
        elseif($type == 'bookings' && module_is_active('Bookings'))
        {
            $duration = [];

            if(!empty($productService))
            {
                $duration = BookingsDuration::where('item_id',$productService->id)->first();
            }
            $returnHTML = view('bookings::item.section', compact('category', 'type', 'unit', 'tax','customFields','productService','action','duration'))->render();
            $response = [
                'is_success' => true,
                'message' => '',
                'html' => $returnHTML,
            ];
            return response()->json($response);
        }
        elseif($type == 'facilities' && module_is_active('Facilities'))
        {
            $spaces          = FacilitiesSpace::where('created_by', '=',creatorId())->where('workspace', '=', getActiveWorkSpace())->get()->pluck('name', 'id');
            $service = [];

            if(!empty($productService))
            {
                $service = FacilitiesService::where('item_id',$productService->id)->first();
            }

            $returnHTML = view('facilities::service.section', compact('category', 'type', 'unit', 'tax','customFields','productService','action','spaces','service'))->render();
            $response = [
                'is_success' => true,
                'message' => '',
                'html' => $returnHTML,
            ];
            return response()->json($response);
        }
        elseif ($type == 'consignment' && module_is_active('ConsignmentManagement')) {
            $weight = [];
            if (!empty($productService)) {
                $weight = ConsignmentWeight::where('item_id', $productService->id)->first();
            }
            $warehouse = Warehouse::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->get()->pluck('name', 'id');
            $warehouse->prepend('Select Warehouse', '');

            $returnHTML = view('consignment-management::item.section', compact('category', 'type', 'unit', 'tax', 'customFields', 'productService', 'action', 'weight','warehouse'))->render();
            $response = [
                'is_success' => true,
                'message' => '',
                'html' => $returnHTML,
            ];
            return response()->json($response);
        }
        elseif ($type == 'optical eyecare' && module_is_active('OpticalAndEyeCareCenter')) {
            $optical = [];

            if(!empty($productService))
            {
                $optical = OpticalEyeCareItems::where('item_id',$productService->id)->first();
            }
            $returnHTML = view('optical-and-eye-care-center::item.section', compact('category', 'type', 'unit', 'tax', 'customFields', 'productService', 'action', 'optical'))->render();
            $response = [
                'is_success' => true,
                'message' => '',
                'html' => $returnHTML,
            ];
            return response()->json($response);
        }
        elseif ($type == 'jewellery store' && module_is_active('JewelleryStoreManagement')) {
            $jewellery_item = [];

            if (!empty($productService)) {
                $jewellery_item = JewelleryItem::where('item_id', $productService->id)->first();
            }
            $returnHTML = view('jewellery-store-management::item.section', compact('category', 'type', 'unit', 'tax', 'customFields', 'productService', 'action', 'jewellery_item'))->render();
            $response = [
                'is_success' => true,
                'message' => '',
                'html' => $returnHTML,
            ];
            return response()->json($response);
        }
        else {
            $response = [
                'is_success' => false,
                'message' => '',
                'html' => '',
            ];
            return response()->json($response);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if(Auth::user()->isAbleTo('product&service create'))
        {
            $rules = [
                'name' => 'required',
                'sku' => 'required',
                'sale_price' => 'required|numeric',
                'purchase_price' => 'required|numeric',
                'category_id' => 'required',
                'unit_id' => 'required',
                'type' => 'required',
                'tax_id' => 'required',
            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('product-service.create')->with('error', $messages->first());
            }


            $productService                 = new ProductService();
            $productService->name           = $request->name;
            $productService->description    = $request->description;
            $productService->sku            = $request->sku;
            if($request->hasFile('image')){
                $name = time() . "_" . $request->image->getClientOriginalName();
                $path = upload_file($request,'image',$name,'products');
                if ($path['flag'] == 1) {
                    $productService->image          = empty($path) ? null : $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

            }

            $productService->sale_price     = $request->sale_price;
            $productService->purchase_price = $request->purchase_price;
            $productService->tax_id         = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
            $productService->unit_id        = $request->unit_id;
            if(!empty($request->quantity))
            {
                $productService->quantity        = $request->quantity;
            }
            else{
                $productService->quantity   = 0;
            }
            $productService->type           = $request->type;
            $productService->category_id    = $request->category_id;
            $productService->warehouse_id    = $request->warehouse_id;
            $productService->created_by     = creatorId();
            $productService->workspace_id     = getActiveWorkSpace();
            $productService->save();

            event(new CreateProduct($request,$productService));

            if ($productService && $productService->type == 'parts') {

                $components_id = $request->components_id;
                $pms_id = $request->pms_id;
                $supplier_id = $request->supplier_id;
                $workorder_id = $request->workorder_id;
                if ($components_id != 0 && !empty($components_id)) {

                    $Components = Component::where(['id' => $components_id, 'company_id' => creatorId(), 'is_active' => 1])->first();
                    if (!is_null($Components)) {
                        $parts_id = [];
                        if (!empty($Components->parts_id)) {
                            $parts_id = explode(',', $Components->parts_id);
                        }
                        $parts_id[] = $productService->id;

                        Component::where('id', $components_id)->update(['parts_id' => implode(',', $parts_id)]);
                    }
                    return redirect()->route('component.show' , $components_id)->with('success', __('The parts has been created successfully.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
                }
                //pms detail page in parts create
                elseif ($pms_id != 0 && !empty($pms_id)) {

                    $Pms = Pms::where(['id' => $pms_id, 'company_id' => creatorId(), 'is_active' => 1])->first();
                    if (!is_null($Pms)) {
                        $parts_id = [];
                        if (!empty($Pms->parts_id)) {
                            $parts_id = explode(',', $Pms->parts_id);
                        }
                        $parts_id[] = $productService->id;
                        Pms::where('id', $pms_id)->update(['parts_id' => implode(',', $parts_id)]);
                    }
                    return redirect()->route('pms.show' , $pms_id)->with('success', __('The parts has been created successfully.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));

                } elseif ($supplier_id != 0 && !empty($supplier_id)) {

                    $Supplier = Supplier::where(['id' => $supplier_id, 'company_id' => creatorId(), 'is_active' => 1])->first();
                    if (!is_null($Supplier)) {
                        $parts_id = [];
                        if (!empty($Supplier->parts_id)) {
                            $parts_id = explode(',', $Supplier->parts_id);
                        }
                        $parts_id[] = $productService->id;
                        Supplier::where('id', $supplier_id)->update(['parts_id' => implode(',', $parts_id)]);
                    }
                    return redirect()->route('supplier.show' , $supplier_id)->with('success', __('The parts has been created successfully.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
                }
                //work order deatil page in parts create
                elseif ($workorder_id != 0 && !empty($workorder_id)) {

                    $WorkOrder = WorkOrder::where(['id' => $workorder_id, 'company_id' => creatorId(), 'is_active' => 1])->first();
                    if (!is_null($WorkOrder)) {
                        $parts_id = [];
                        if (!empty($WorkOrder->parts_id)) {
                            $parts_id = explode(',', $WorkOrder->parts_id);
                        }
                        $parts_id[] = $productService->id;

                        WorkOrder::where('id', $workorder_id)->update(['parts_id' => implode(',', $parts_id)]);
                    }
                    return redirect()->route('workorder.show' , $workorder_id)->with('success', __('The parts has been created successfully.') . ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : ''));
                }
            }

            if(module_is_active('CustomField'))
            {
                \Workdo\CustomField\Entities\CustomField::saveData($productService, $request->customField);
            }

            if(!empty($request->warehouse_id))
            {
                Purchase::addWarehouseStock( $productService->id, $request->quantity, $request->warehouse_id);
            }
            return redirect()->route('product-service.index')->with('success', __('The product has been created successfully.'));
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
         if (\Auth::user()->isAbleTo('product&service edit')) {
             $productService = ProductService::find($id);
             $vendors = User::where('workspace_id',getActiveWorkSpace())
                 ->leftjoin('vendors', 'users.id', '=', 'vendors.user_id')
                 ->where('users.type', 'vendor')
                 ->select('users.*','vendors.*', 'users.name as name', 'users.email as email', 'users.id as id','users.mobile_no as contact')
                 ->get();
             $purchases =  Purchase::where('created_by', creatorId())->where('workspace', getActiveWorkSpace())->with('vender', 'category', 'user')->get();

            $total_sold = 0;
            $total_cost = null;
            $total_products_use = 0;
            if(module_is_active('Pos')){

                $total_sold = PosProduct::where('product_id', $id)->sum('quantity');
                $total_cost = PosProduct::select(DB::raw('SUM(price*quantity+(price*quantity*tax/100)-discount) as total_cost'))->where('product_id', $id)->first();
                $total_products_use = PosProduct::where('product_id', $id)->count();
            }

             $productslogtime = ProductsLogTime::where('product_id', $id)->get();
             $products = WarehouseProduct::where('product_id', '=', $id)->where('created_by',creatorId())->where('workspace',getActiveWorkSpace())->get();

             return view('product-service::view', compact('productService','vendors','purchases','total_sold','total_cost','total_products_use','productslogtime','products'));
         } else {
             return redirect()->back()->with('error', __("Permission Denied"));
         }
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if(Auth::user()->isAbleTo('product&service edit'))
        {
            $productService = ProductService::find($id);
            return view('product-service::edit', compact('productService'));
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
    public function update(Request $request, $id)
    {
        if(Auth::user()->isAbleTo('product&service edit'))
        {
            $productService = ProductService::find($id);

            $rules = [
                'name' => 'required',
                'sku' => 'required',
                'sale_price' => 'required|numeric',
                'purchase_price' => 'required|numeric',
                'category_id' => 'required',
                'unit_id' => 'required',
                'type' => 'required',
                'tax_id' => 'required',

            ];

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('product-service.index')->with('error', $messages->first());
            }


            $old_qty = $productService->quantity;
            $new_qty = $request->quantity;
            $total_qty = $new_qty - $old_qty;
            if (!empty($request->warehouse_id)) {

                Purchase::addWarehouseStock($productService->id, $total_qty, $request->warehouse_id);
            }

            $productService->name           = $request->name;
            $productService->description    = $request->description;
            $productService->sku            = $request->sku;
            $productService->sale_price     = $request->sale_price;
            $productService->purchase_price = $request->purchase_price;
            $productService->tax_id         = !empty($request->tax_id) ? implode(',', $request->tax_id) : '';
            $productService->unit_id        = $request->unit_id;
            $productService->quantity        = $request->quantity;
            $productService->type           = $request->type;
            if($request->hasFile('image'))
            {
                // old file delete
                if(!empty($productService->image))
                {
                    delete_file($productService->image);
                }

                $name = time() . "_" . $request->image->getClientOriginalName();
                $path = upload_file($request,'image',$name,'products');
                if ($path['flag'] == 1) {
                    $productService->image          = empty($path) ? null : $path['url'];
                } else {
                    return redirect()->back()->with('error', __($path['msg']));
                }

            }
            $productService->category_id    = $request->category_id;
            $productService->warehouse_id    = $request->warehouse_id;
            $productService->save();

            event(new UpdateProduct($request,$productService));

            if(module_is_active('CustomField'))
            {
                \Workdo\CustomField\Entities\CustomField::saveData($productService, $request->customField);
            }
            return redirect()->route('product-service.index')->with('success', __('The product details are updated successfully.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function grid(Request $request)
    {
        if(Auth::user()->isAbleTo('product&service manage'))
        {
            $category = Category::where('created_by', '=', creatorId())->where('workspace_id', '=', getActiveWorkSpace())->where('type', '=', 0)->get()->pluck('name', 'id');
            $product_type = ProductService::$product_type;
            if(module_is_active('RentalManagement')) {
                $product_type['rent'] = 'Rent';
            }
            if(module_is_active('MusicInstitute')) {
                $product_type['music institute'] = 'Music Institute';
            }
            if(module_is_active('Bookings')) {
                $product_type['bookings'] = 'Bookings';
            }
            if(module_is_active('Fleet')) {
                $product_type['fleet'] = 'Fleet';
            }
            if(module_is_active('Facilities')) {
                $product_type['facilities'] = 'Facilities';
            }
            if (module_is_active('OpticalAndEyeCareCenter')) {
                $product_type['optical eyecare'] = 'Optical & Eye Care';
            }
            if (module_is_active(('JewelleryStoreManagement'))) {
                $product_type['jewellery store'] = 'Jewellery Store';
            }

            $productServices = ProductService::select('product_services.*', DB::raw('GROUP_CONCAT(taxes.name) as tax_names'))
            ->leftJoin('taxes', function ($join) {
                $join->on('taxes.id', '=', DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(product_services.tax_id, ',', numbers.n), ',', -1)"))
                    ->crossJoin(DB::raw('(SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) numbers'))
                    ->whereRaw('CHAR_LENGTH(product_services.tax_id) - CHAR_LENGTH(REPLACE(product_services.tax_id, ",", "")) + 1 >= numbers.n');
            })
            ->where('product_services.created_by', creatorId())
            ->where('product_services.workspace_id', getActiveWorkSpace())
            ->groupBy('product_services.id');
            if (!empty($request->category))
            {
                $productServices = $productServices->where('product_services.category_id', $request->category);
            }
            if (!empty($request->item_type))
            {
                $productServices = $productServices->where('product_services.type', $request->item_type);
            }
                $productServices = $productServices->paginate(11);

            return view('product-service::grid',compact('productServices', 'category','product_type'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }



    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if(Auth::user()->isAbleTo('product&service delete'))
        {
            $invoice_product=InvoiceProduct::where('product_id',$id)->first();
            $proposal_product=ProposalProduct::where('product_id',$id)->first();
            $data = event(new DeleteProductService($id));

            if((empty($invoice_product) && empty($proposal_product)) && !in_array('false',array_filter($data))){
            $productService = ProductService::find($id);
            if(!empty($productService->image))
            {
                delete_file($productService->image);
            }
            event(new DestroyProduct($productService));
            $productService->delete();
            return redirect()->back()->with('success', __('The product has been deleted.'));
        }else{

            return redirect()->back()->with('error', __('Please delete'.(!empty($invoice_product) ? ' Invoice ' : '').(!empty($proposal_product) ? ' and Proposal ' : '').'related record of this Product.'));
        }
        }
        else
        {

            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function fileImportExport()
    {
        if(Auth::user()->isAbleTo('product&service import'))
        {
            return view('product-service::import');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function fileImport(Request $request)
    {
        if(Auth::user()->isAbleTo('product&service import'))
        {
            session_start();

            $error = '';

            $html = '';

            if ($request->file->getClientOriginalName() != '') {
                $file_array = explode(".", $request->file->getClientOriginalName());

                $extension = end($file_array);
                if ($extension == 'csv') {
                    $file_data = fopen($request->file->getRealPath(), 'r');

                    $file_header = fgetcsv($file_data);
                    $html .= '<table class="table table-bordered"><tr>';

                    for ($count = 0; $count < count($file_header); $count++) {
                        $html .= '
                                <th>
                                    <select name="set_column_data" class="form-control set_column_data" data-column_number="' . $count . '">
                                    <option value="">Set Count Data</option>
                                    <option value="name">Name</option>
                                    <option value="sku">SKU</option>
                                    <option value="sale_price">Sale Price</option>
                                    <option value="purchase_price">Purchase Price</option>
                                    <option value="quantity">Quantity</option>
                                    <option value="type">Type</option>
                                    <option value="description">Description</option>
                                    </select>
                                </th>
                                ';

                    }
                    $html .= '</tr>';
                    $limit = 0;
                    while (($row = fgetcsv($file_data)) !== false) {
                        $limit++;

                        $html .= '<tr>';

                        for ($count = 0; $count < count($row); $count++) {
                            $html .= '<td>' . $row[$count] . '</td>';
                        }

                        $html .= '</tr>';

                        $temp_data[] = $row;

                    }
                    $_SESSION['file_data'] = $temp_data;
                } else {
                    $error = 'Only <b>.csv</b> file allowed';
                }
            } else {

                $error = 'Please Select CSV File';
            }
            $output = array(
                'error' => $error,
                'output' => $html,
            );

            return json_encode($output);
        }
        else
        {
            return redirect()->back()->with('error', 'permission Denied');
        }

    }

    public function fileImportModal()
    {
        if(Auth::user()->isAbleTo('product&service import'))
        {
            return view('product-service::import_modal');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function productserviceImportdata(Request $request)
    {
        if(Auth::user()->isAbleTo('product&service import'))
        {
            session_start();
            $html = '<h3 class="text-danger text-center">Below data is not inserted</h3></br>';
            $flag = 0;
            $html .= '<table class="table table-bordered"><tr>';
            $file_data = $_SESSION['file_data'];

            unset($_SESSION['file_data']);

            $user = \Auth::user();


            foreach ($file_data as $row) {
                    $product = ProductService::where('created_by',creatorId())->where('workspace_id',getActiveWorkSpace())->Where('name', 'like',$row[$request->name])->get();

                    if($product->isEmpty()){

                    try {
                        ProductService::create([
                            'name' => $row[$request->name],
                            'sku' => $row[$request->sku],
                            'sale_price' => $row[$request->sale_price],
                            'purchase_price' => $row[$request->purchase_price],
                            'quantity' => $row[$request->quantity],
                            'type' => $row[$request->type],
                            'description' => $row[$request->description],
                            'created_by' => creatorId(),
                            'workspace_id' => getActiveWorkSpace(),
                        ]);
                    }
                    catch (\Exception $e)
                    {
                        $flag = 1;
                        $html .= '<tr>';

                        $html .= '<td>' . $row[$request->name] . '</td>';
                        $html .= '<td>' . $row[$request->sku] . '</td>';
                        $html .= '<td>' . $row[$request->sale_price] . '</td>';
                        $html .= '<td>' . $row[$request->purchase_price] . '</td>';
                        $html .= '<td>' . $row[$request->quantity] . '</td>';
                        $html .= '<td>' . $row[$request->type] . '</td>';
                        $html .= '<td>' . $row[$request->description] . '</td>';

                        $html .= '</tr>';
                    }
                }
                else
                {
                    $flag = 1;
                    $html .= '<tr>';

                    $html .= '<td>' . $row[$request->name] . '</td>';
                    $html .= '<td>' . $row[$request->sku] . '</td>';
                    $html .= '<td>' . $row[$request->sale_price] . '</td>';
                    $html .= '<td>' . $row[$request->purchase_price] . '</td>';
                    $html .= '<td>' . $row[$request->quantity] . '</td>';
                    $html .= '<td>' . $row[$request->type] . '</td>';
                    $html .= '<td>' . $row[$request->description] . '</td>';

                    $html .= '</tr>';
                }
            }

            $html .= '
                            </table>
                            <br />
                            ';
            if ($flag == 1)
            {

                return response()->json([
                            'html' => true,
                    'response' => $html,
                ]);
            } else {
                return response()->json([
                    'html' => false,
                    'response' => 'Data Imported Successfully',
                ]);
            }
        }
        else
        {
            return redirect()->back()->with('error', 'permission Denied');
        }
    }

    public function GetItem(Request $request)
    {
        $product_services = \Workdo\ProductService\Entities\ProductService::where('workspace_id', getActiveWorkSpace());
        if (module_is_active('CMMS')) {
            $product_type['parts'] = 'Parts';
        }
        if(!empty($request->product_type)){
            $product_services = $product_services->where('type',$request->product_type);
        }
        $product_services = $product_services->get()->pluck('name', 'id');
        return response()->json($product_services);
    }

    public function getTaxes(Request $request)
    {
        if(module_is_active('ProductService'))
        {
            $taxs_data = \Workdo\ProductService\Entities\Tax::whereIn('id',$request->tax_id)->where('workspace_id', getActiveWorkSpace())->select('id', 'name', 'rate')->get();
            return json_encode($taxs_data);
        }else{
            $taxs_data = [];
            return json_encode($taxs_data);
        }
    }




}
