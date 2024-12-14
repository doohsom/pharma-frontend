<?php

namespace App\Http\Controllers;

use App\Services\OrderApiService;
use App\Services\ProductApiService;
use App\Services\UserApiService;
use Illuminate\Http\Request;
use Exception;

class OrderController extends Controller
{
    protected $orderApiService;
    protected $productApiService;
    protected $userApiService;

    public function __construct(
        OrderApiService $orderApiService,
        ProductApiService $productApiService,
        UserApiService $userApiService
    ) {
        $this->orderApiService = $orderApiService;
        $this->productApiService = $productApiService;
        $this->userApiService = $userApiService;
    }

    public function index()
    {
        try {
            $orders = $this->orderApiService->getAllOrders();
            return view('orders.index', compact('orders'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $products = $this->productApiService->getAllProducts();
            $users = $this->userApiService->getAllUsers();
            return view('orders.create', compact('products', 'users'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'receiver_id' => 'required|exists:users,id',
                'quantity' => 'required|integer|min:1',
                'status' => 'required|in:pending,processing,completed,cancelled',
            ]);

            $validated['sender_id'] = auth()->id();
            
            $order = $this->orderApiService->createOrder($validated);
            return redirect()->route('orders.index')->with('success', 'Order created successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        try {
            $order = $this->orderApiService->getOrder($id);
            return view('orders.show', compact('order'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $order = $this->orderApiService->getOrder($id);
            $products = $this->productApiService->getAllProducts();
            $users = $this->userApiService->getAllUsers();
            return view('orders.edit', compact('order', 'products', 'users'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'receiver_id' => 'required|exists:users,id',
                'quantity' => 'required|integer|min:1',
                'status' => 'required|in:pending,processing,completed,cancelled',
            ]);

            $order = $this->orderApiService->updateOrder($id, $validated);
            return redirect()->route('orders.index')->with('success', 'Order updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $this->orderApiService->deleteOrder($id);
            return redirect()->route('orders.index')->with('success', 'Order deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}