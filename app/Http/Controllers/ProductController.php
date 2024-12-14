<?php

namespace App\Http\Controllers;

use App\Services\ProductApiService;
use Illuminate\Http\Request;
use Exception;

class ProductController extends Controller
{
    protected $productApiService;

    public function __construct(ProductApiService $productApiService)
    {
        $this->productApiService = $productApiService;
    }

    public function index()
    {
        try {
            $products = $this->productApiService->getAllProducts();
            return view('products.index', compact('products'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|string',
                'manufacture_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:manufacture_date',
                'price' => 'required|numeric|min:0',
                'status' => 'required|string|in:active,inactive',
            ]);

            $validated['user_id'] = auth()->id();
            
            $product = $this->productApiService->createProduct($validated);
            return redirect()->route('products.index')->with('success', 'Product created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $product = $this->productApiService->getProduct($id);
            return view('products.show', compact('product'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $product = $this->productApiService->getProduct($id);
            return view('products.edit', compact('product'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|string',
                'manufacture_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:manufacture_date',
                'price' => 'required|numeric|min:0',
                'status' => 'required|string|in:active,inactive',
            ]);

            $product = $this->productApiService->updateProduct($id, $validated);
            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $this->productApiService->deleteProduct($id);
            return redirect()->route('products.index')->with('success', 'Product deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}