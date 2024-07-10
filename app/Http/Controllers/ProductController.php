<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Fetch products along with their category details
        $products = Product::with('category')->get();

        return response()->json($products);
    }

    /**
     * Store a newly created product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'image' => 'nullable|file|image|max:2048',
            'code' => 'required|unique:products|max:255',
        ]);

        $data = $request->all();

        // Handle image upload if included in the request
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('images', 'public');
        }

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    /**
     * Display the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Fetch a single product along with its category details
        $product = Product::with('category')->findOrFail($id);

        return response()->json($product);
    }

    /**
     * Update the specified product in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'image' => 'nullable|image|max:2048', // Allow image file, max size 2MB
            'code' => 'required|max:255|unique:products,code,' . $id,
        ]);

        // Find the product by ID
        $product = Product::findOrFail($id);

        // Update product fields based on incoming request data
        $product->name = $request->input('name');
        $product->category_id = $request->input('category_id');
        $product->price = $request->input('price');
        $product->code = $request->input('code');

        // Handle image update if new image is uploaded
        if ($request->hasFile('image')) {
            // Delete the old image if it existst
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Store the new image
            $product->image = $request->file('image')->store('images', 'public');
        }

        // Save the updated product
        $product->save();

        // Return the updated product as JSON response
        return response()->json($product);
    }
    /**
     * Remove the specified product from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete associated image if exists
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return response()->json(null, 204);
    }
}
