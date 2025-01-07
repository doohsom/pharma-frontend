<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Services\UserApiService;
use App\Services\OrderApiService;
use App\Services\ProductApiService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $orderService;
    protected $productService;
    protected $userApiService;

    public function __construct(
        OrderApiService $orderService,
        ProductApiService $productService,
        UserApiService $userApiService
    ) {
        $this->orderService = $orderService;
        $this->productService = $productService;
        $this->userApiService = $userApiService;
    }

   // OrderController.php
   public function index()
   {
        try {
           $user = auth()->user();
           Log::info('User info:', [
               'user_id' => $user->id,
               'role' => $user->role,
               'blockchain_id' => $user->blockchain_id
           ]);
   
           $orders = $this->orderService->getAllOrders();          


           Log::info('Raw orders from API:', ['orders' => $orders]);
   
           // Filter orders based on role
           $filteredOrders = collect($orders)->filter(function ($order) use ($user) {
               Log::info('Processing order:', [
                   'order' => $order,
                   'user_role' => $user->role,
                   'user_blockchain_id' => $user->blockchain_id
               ]);
               
               switch ($user->role) {
                   case 'vendor':
                       return $order['senderId'] === $user->blockchain_id;
                   case 'manufacturer':
                       return in_array($order['status'], ['pending', 'in_batch','to_be_batched','processed', 'in_transit']);
                       //return $order['receiverId'] === $user->blockchain_id;
                   case 'logistics':
                       return in_array($order['status'], ['to_be_batched', 'in_batch', 'in_transit']);
                   default:
                       return false;
               }
           })->values()->all();
   
           Log::info('Filtered orders:', ['filtered_orders' => $filteredOrders]);
   
           return view('orders.index', [
               'orders' => $filteredOrders,
               'userRole' => $user->role
           ]);
        } catch (Exception $e) {
           Log::error('Error in orders index:', [
               'error' => $e->getMessage(),
               'trace' => $e->getTraceAsString()
           ]);
           return redirect()->back()->with('error', 'Failed to load orders');
        }
    }


    public function create(Request $request)
    {
        try {
            $productId = $request->query('product');
            if (!$productId) {
                return redirect()->route('products.index')
                    ->with('error', 'Please select a product to order');
            }

            $product = $this->productService->getProductById($productId);
            if (!$product || $product['status'] !== 'approved') {
                return redirect()->route('products.index')
                    ->with('error', 'Product not found or not available for ordering');
            }
 
            return view('orders.create', compact('product'));

        } catch (\Exception $e) {
            Log::error('Error in order creation form:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('products.index')
                ->with('error', 'An error occurred while preparing the order form');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|string',
                'quantity' => 'required|numeric|min:0.01|max:999999.99',
                'notes' => 'nullable|string|max:500'
            ]);

            // Get product details first
            $product = $this->productService->getProductById($validated['product_id']);
            if (!$product) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Product not found');
            }

            // Verify product status
            if ($product['status'] !== 'approved') {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'This product is not available for ordering');
            }

            // Create order with proper data structure
            $orderData = [
                'product_id' => $validated['product_id'],
                'sender_id' => auth()->user()->blockchain_id,
                'receiver_id' => $product['userId'],
                'quantity' => (float) $validated['quantity'],
                'notes' => $validated['notes'] ?? ''
            ];

            Log::info('Creating order:', [
                'order_data' => $orderData,
                'user' => auth()->user()->blockchain_id
            ]);

            $order = $this->orderService->createOrder($orderData);

            return redirect()->route('orders.index', $order['id'])
                ->with('success', 'Order created successfully');

        } catch (Exception $e) {
            Log::error('Order creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $message = $this->getReadableErrorMessage($e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', $message);
        }
    }

    private function getReadableErrorMessage($error)
    {
        if (str_contains($error, 'already exists')) {
            return 'Unable to create order. Please try again.';
        }
        if (str_contains($error, 'does not exist')) {
            return 'Invalid product or user reference.';
        }
        if (str_contains($error, 'failed to endorse transaction')) {
            return 'Transaction validation failed. Please try again.';
        }
        return 'Failed to create order. Please try again later.';
    }

    public function show($id)
    {
        try {
            Log::info('Fetching order details:', ['order_id' => $id]);
            
            $order = $this->orderService->getOrder($id);
            
            if (!$order) {
                Log::error('Order not found:', ['order_id' => $id]);
                return response()->json(['message' => 'Order not found'], 404);
            }
            
            // Log the exact structure being returned
            Log::info('Order response structure:', [
                'order' => $order,
                'type' => gettype($order),
                'json' => json_encode($order)
            ]);
            
            return response()->json([
                [
                    'order' => $order,
                    'product' => null, // Add if available
                    'sender' => null,  // Add if available
                    'receiver' => null // Add if available
                ]
            ]);
            
        } catch (Exception $e) {
            Log::error('Error fetching order:', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to fetch order details'
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $order = $this->orderService->getOrder($id);
            $products = $this->productService->getAllProducts();
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

            $order = $this->orderService->updateOrder($id, $validated);
            return redirect()->route('orders.index')->with('success', 'Order updated successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    // OrderController.php
    private $validStatuses = [
        'pending' => true,
        'processed' => true,
        'to_be_batched' => true,
        'in_transit' => true,
        'delivered' => true
    ];

    public function updateStatus(Request $request, $id)
    {
        try {
            // Validate request
            $validated = $request->validate([
                'status' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if (!isset($this->validStatuses[$value])) {
                            $fail('Invalid status provided. Valid statuses are: ' . implode(', ', array_keys($this->validStatuses)));
                        }
                    }
                ],
                'notes' => 'required|string|min:10'
            ]);

            $result = $this->orderService->updateOrderStatus($id, [
                'status' => $validated['status']
                // Keep notes and updatedBy for your local records if needed
            ]);

            return response()->json([
                'message' => 'Order status updated successfully',
                'order' => $result
            ]);

        } catch (Exception $e) {
            Log::error('Order status update failed:', [
                'error' => $e->getMessage(),
                'order_id' => $id
            ]);

            return response()->json([
                'message' => 'Failed to update order status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->orderService->deleteOrder($id);
            return redirect()->route('orders.index')->with('success', 'Order deleted successfully');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}