<?php

namespace App\Http\Controllers\admin;

use App\Exports\ExportShipping;
use App\Http\Controllers\Controller;
use App\Imports\ShippingImport;
use App\Models\Country;
use App\Models\ShippingCharge;
use PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

use function Laravel\Prompts\error;

class ShippingController extends Controller
{
    public function create() {
        $countries = Country::get();
        $data['countries'] = $countries;

        $shippingCharges = ShippingCharge::select('shipping_charges.*','countries.name')
                    ->leftJoin('countries', 'countries.id', 'shipping_charges.country_id')->get();
       
        $data['shippingCharges'] = $shippingCharges;
        return view('admin.shipping.create',$data);
    }

    public function store(Request $request) {

        
        $validator = Validator::make($request->all(),[
            'country' => 'required',
            'amount' => 'required|numeric'
        ]);

        if ($validator->passes()) {

            $count = ShippingCharge::where('country_id',$request->country)->count();
            if($count > 0) {
            session()->flash('error', 'Shipping Already Added');
            return response()->json([
                'status' => true
                ]);
            }

            $shipping = new ShippingCharge();
            $shipping->country_id = $request->country;
            $shipping->amount = $request->amount;
            $shipping->save();

            session()->flash('success', 'Shipping Added Successfully');

            return response()->json([
                'status' => true,
            ]);

        }  else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($id) {
        
        $shippingCharge  = ShippingCharge::find($id);

        $countries = Country::get();
        $data['countries'] = $countries;
        $data['shippingCharge'] = $shippingCharge;


        return view('admin.shipping.edit',$data);
    }

    public function update($id, Request $request) {

        $shipping = ShippingCharge::find($id);

        $validator = Validator::make($request->all(),[
            'country' => 'required',
            'amount' => 'required|numeric'
        ]);

        if ($validator->passes()) {

            if ($shipping == null) {

                session()->flash('error', 'Shipping Not Found');
    
                return response()->json([
                'status' => true,
            ]);
    
            }

            
            $shipping->country_id = $request->country;
            $shipping->amount = $request->amount;
            $shipping->save();

            session()->flash('success', 'Shipping Updated Successfully');

            return response()->json([
                'status' => true,
            ]);

        }  else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id) {
        $shippingCharge =  ShippingCharge::find($id);

        if ($shippingCharge == null) {
            session()->flash('error', 'Shipping Not Found');

            return response()->json([
            'status' => true,
        ]);       

        }

        $shippingCharge->delete();

        session()->flash('success', 'Shipping Deleted Successfully');

        return response()->json([
            'status' => true,
        ]);
    }

    public function export_excel(){
       return Excel::download(new ExportShipping, "shipping.xlsx");
    }

    public function import_excel()
    {
        try {
            Excel::import(new ShippingImport, request()->file('file'));
            session()->flash('success', 'Excel file imported successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error importing Excel file: ' . $e->getMessage());
        }

        return back();
    }

    public function export_pdf()
    {
        // Retrieve order details and customer information
        $shippingCharges = ShippingCharge::with('country')->get();
        $data['shippingCharges'] = $shippingCharges;
        $now = Carbon::now()->format('Y-m-d');
        $data['now'] = $now;

        // Generate the PDF
        $pdf = PDF::loadView('admin.report.shipping', compact('data'));

        // Set options if needed (e.g., page size, orientation)
        $pdf->setPaper('A4', 'potrait');
        
        // Download the PDF with a specific filename
        return $pdf->stream('Shipping.pdf');
    }
}