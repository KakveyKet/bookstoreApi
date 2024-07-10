<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Models\Purchase;

class CartController extends Controller
{
    /**
     * Add a product to the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {
        $user = $request->user();
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1); // Default quantity to 1 if not provided

        // Check if the product is already in the cart
        $existingItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingItem) {
            // Update the quantity if the item already exists in the cart
            $existingItem->quantity += $quantity;
            $existingItem->save();
        } else {
            // Create a new cart item
            CartItem::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        return response()->json(['message' => 'Item added to cart successfully.']);
    }
    /**
     * Remove a product from the cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromCart($productId)
    {
        $user = auth()->user();

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $cartItem->delete();
            return response()->json(['message' => 'Item removed from cart']);
        } else {
            return response()->json(['message' => 'Item not found'], 404);
        }
    }

    /**
     * Get all cart items for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCart(Request $request)
    {
        $user = $request->user();
        $cartItems = CartItem::where('user_id', $user->id)
            ->with('product.category') // Ensure category is eager loaded
            ->get();

        return response()->json(['cartItems' => $cartItems]);

        // Return the formatted response as JSON
        // return response()->json(['cartItems' => $formattedCartItems]);
    }
    /**
     * Clear the cart for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCart(Request $request)
    {
        $user = $request->user();
        CartItem::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Cart cleared successfully']);
    }
    public function checkout(Request $request)
    {
        $user = $request->user();

        // Retrieve cart items for the current user
        $cartItems = CartItem::where('user_id', $user->id)->get();

        foreach ($cartItems as $cartItem) {
            // Create a purchase record for each cart item
            $purchase = Purchase::create([
                'user_id' => $user->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
            ]);

            // Fetch the product details including image_url
            $product = $purchase->product()->with('category')->first();

            // Add product_image to the purchase data
            $purchase->product_image = $product->image_url ?? null; // Adjust based on your Product model

            // Remove item from cart after purchase
            $cartItem->delete();
        }

        return response()->json(['message' => 'Checkout successful.']);
    }
}
