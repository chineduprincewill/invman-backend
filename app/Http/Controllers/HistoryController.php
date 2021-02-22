<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\History;
use App\Inventory;

class HistoryController extends Controller
{
    //
    public function show($id)
    {
        $history = History::where('item_id', $id)->orderBy('created_at', 'desc')->get();
        $total_disbursed = History::where('item_id', $id)->sum('quantity_disbursed');

        return response()->json([
                "disbursements" => $history, 
                "count" => $total_disbursed
        ], 200);
    }

    public function disburse(Request $request, $item_id)
    {

        $inventory = Inventory::find($item_id);

        // check if quantity to be disbursed is in stock
        if($inventory->quantity < $request->get('quantity')){

            return response()->json([
                'success' => false,
                'message' => 'Insufficient item in stock for disbursement!'
            ], 401);
        }
        else{

            // disburse item
            $history = History::create([
                'item_id' => $item_id,
                'quantity_disbursed' => $request->get('quantity'),
                'disbursed_to' => $request->get('to'),
                'purpose' =>$request->get('purpose'),
                'disbursed_by' => $request->get('disburser')
            ]);

            if($history){

                $current_quantity = $inventory->current_quantity - $request->get('quantity');
                $inventory->last_disbursed = date('Y-m-d');
                $inventory->quantity_disbursed = $request->get('quantity');
                $inventory->date_disbursed = date('Y-m-d');
                $inventory->disbursed_to = $request->get('to');
                $inventory->purpose = $request->get('purpose');
                $inventory->current_quantity = $current_quantity;

                $inventory->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Disbursement completed successfully!'
                ], 201);
            }
            else{
                return response()->json([
                    'success' => false,
                    'message' => 'Disbursement could not be completed!'
                ], 401);
            }
        }
        
    }
}
