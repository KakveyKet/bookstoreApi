<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['user', 'product.category'])
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'user_name' => $purchase->user->name,
                    'product_name' => $purchase->product->name,
                    'category_name' => $purchase->product->category->name,
                    'product_image' => $purchase->product->image_url, // Add this line for product image
                    'quantity' => $purchase->quantity,
                    'price' => $purchase->product->price,
                    'total_price' => $purchase->quantity * $purchase->product->price,
                    'created_at' => $purchase->created_at,
                    'updated_at' => $purchase->updated_at,
                ];
            });

        return response()->json(['purchases' => $purchases]);
    }

    public function show($id)
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }
        return response()->json(['purchase' => $purchase]);
    }

    public function destroy($id)
    {
        $purchase = Purchase::find($id);
        if (!$purchase) {
            return response()->json(['message' => 'Purchase not found'], 404);
        }
        $purchase->delete();
        return response()->json(['message' => 'Purchase deleted successfully']);
    }
}
