<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\ProductApiService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;

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
            
            return view('products.index', [
                'products' => $products,
                'filterInfo' => [
                    'role' => auth()->user()->role,
                    'count' => count($products)
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Error in products index:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('products.index', [
                'products' => [],
                'error' => 'Failed to load products. Please try again later.'
            ]);
        }
    }

    public function create()
    {
        return view('products.create');
    }

    public function stores(Request $request)
    {
        try {
            // Validate basic product information
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'manufacture_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:manufacture_date',
                'price' => 'required|numeric|min:0',
                'status' => 'required|string',
                'notes' => 'nullable|string',
                'requirements' => 'required|json'
            ]);

            // Decode and validate storage requirements
            $requirements = json_decode($validated['requirements'], true);
            if (empty($requirements)) {
                throw new ValidationException(null, ['requirements' => 'At least one storage requirement is required.']);
            }
            if (count($requirements) > 3) {
                throw new ValidationException(null, ['requirements' => 'Maximum of 3 storage requirements allowed.']);
            }

            // Validate each requirement
            $usedTypes = [];
            foreach ($requirements as $requirement) {
                if (!isset($requirement['type'], $requirement['min'], $requirement['max'], $requirement['value'])) {
                    throw new ValidationException(null, ['requirements' => 'Invalid requirement format.']);
                }

                if (!in_array($requirement['type'], ['temperature', 'humidity', 'pressure'])) {
                    throw new ValidationException(null, ['requirements' => 'Invalid requirement type.']);
                }

                if (in_array($requirement['type'], $usedTypes)) {
                    throw new ValidationException(null, ['requirements' => 'Duplicate requirement type found.']);
                }

                if ($requirement['min'] >= $requirement['max']) {
                    throw new ValidationException(null, ['requirements' => 'Minimum value must be less than maximum value.']);
                }

                if ($requirement['value'] < $requirement['min'] || $requirement['value'] > $requirement['max']) {
                    throw new ValidationException(null, ['requirements' => 'Target value must be between minimum and maximum values.']);
                }

                $usedTypes[] = $requirement['type'];
            }

            // Prepare data for API
            $productData = [
                'id' => 'PROD_' . uniqid(),
                'name' => $validated['name'],
                'description' => $validated['description'],
                'userId' => auth()->id(),
                'manufactureDate' => Carbon::parse($validated['manufacture_date'])->format('Y-m-d\TH:i:s\Z'),
                'expiryDate' => $validated['expiry_date'] 
                    ? Carbon::parse($validated['expiry_date'])->format('Y-m-d\TH:i:s\Z')
                    : null,
                'price' => (float) $validated['price'],
                'notes' => $validated['notes'] ?? '',
                'requirements' => array_map(function($req) {
                    return [
                        'type' => $req['type'],
                        'min' => (float) $req['min'],
                        'max' => (float) $req['max'],
                        'value' => (float) $req['value']
                    ];
                }, $requirements)
            ];

            // Create product through API service
            $product = $this->productApiService->createProduct($productData);

            return redirect()->route('products.index')->with('success', 'Product created successfully');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->getMessage())
                ->withInput();
        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    public function store(Request $request)
    {
        try {
            // Get authenticated user's blockchain_id
            $user = auth()->user();
            if (!$user || !$user->blockchain_id) {
                throw new Exception('User blockchain ID not found');
            }

            // Validate basic product information
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'manufacture_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:manufacture_date',
                'price' => 'required|numeric|min:0',
                'status' => 'required|string|in:pending,for_approval,approved,rejected,inactive',
                'notes' => 'nullable|string',
                'requirements' => 'required|json'
            ]);

            // Decode and validate storage requirements
            $requirements = json_decode($validated['requirements'], true);
            if (empty($requirements)) {
                throw new ValidationException(null, ['requirements' => 'At least one storage requirement is required.']);
            }
            if (count($requirements) > 3) {
                throw new ValidationException(null, ['requirements' => 'Maximum of 3 storage requirements allowed.']);
            }

            // Validate each requirement
            $usedTypes = [];
            foreach ($requirements as $requirement) {
                if (!isset($requirement['type'], $requirement['min'], $requirement['max'], $requirement['value'])) {
                    throw new ValidationException(null, ['requirements' => 'Invalid requirement format.']);
                }

                if (!in_array($requirement['type'], ['temperature', 'humidity', 'pressure'])) {
                    throw new ValidationException(null, ['requirements' => 'Invalid requirement type.']);
                }

                if (in_array($requirement['type'], $usedTypes)) {
                    throw new ValidationException(null, ['requirements' => 'Duplicate requirement type found.']);
                }

                // Validate units based on type
                $validUnits = [
                    'temperature' => ['celsius', 'fahrenheit'],
                    'humidity' => ['relative_humidity', 'absolute_humidity'],
                    'pressure' => ['hectopascals', 'millibars', 'psi']
                ];

                if (!in_array($requirement['value'], $validUnits[$requirement['type']])) {
                    throw new ValidationException(null, [
                        'requirements' => "Invalid unit for {$requirement['type']}: {$requirement['value']}"
                    ]);
                }

                if ($requirement['min'] >= $requirement['max']) {
                    throw new ValidationException(null, [
                        'requirements' => "Minimum value must be less than maximum value for {$requirement['type']}"
                    ]);
                }

                $usedTypes[] = $requirement['type'];
            }

            // Prepare data for API
            $productData = [
                'id' => 'PROD_' . uniqid(),
                'name' => $validated['name'],
                'description' => $validated['description'],
                'userId' => $user->blockchain_id, // Using the authenticated user's blockchain_id
                'manufactureDate' => Carbon::parse($validated['manufacture_date'])->format('Y-m-d\TH:i:s\Z'),
                'expiryDate' => $validated['expiry_date'] 
                    ? Carbon::parse($validated['expiry_date'])->format('Y-m-d\TH:i:s\Z')
                    : null,
                'price' => (float) $validated['price'],
                'requirements' => array_map(function($req) {
                    return [
                        'type' => $req['type'],
                        'min' => (float) $req['min'],
                        'max' => (float) $req['max'],
                        'value' => (float) $req['value']
                    ];
                }, $requirements),
                'notes' => $validated['notes'] ?? '',
                'status' => $validated['status']
            ];

            Log::info('Creating product with data:', ['productData' => $productData]);

            // Create product through API service
            $product = $this->productApiService->createProduct($productData);

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (Exception $e) {
            Log::error('Product creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create product. ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $product = $this->productApiService->getProduct($id);
            Log::info($product);
            return response()->json([
                [  // Wrap in array to match expected format
                    'product' => $product['product'],
                    'user' => $product['user']
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
        Log::info($request);
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'manufacture_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:manufacture_date',
                'price' => 'required|numeric|min:0',
                'status' => 'required|string|in:pending,for_approval,approved,rejected,inactive',
                'notes' => 'nullable|string',
                'requirements' => 'required|json'
            ]);
            
            $product = $this->productApiService->updateProduct($id, $validated);
            Log::info($product);
            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

  
    public function updateStatus(Request $request, $id)
    {
        try {
            
            $user = auth()->user();
            Log::info('Status update request received:', [
                'request_data' => $request->all(),
                'user_role' => $user->role
            ]);
            $currentProduct = $this->productApiService->getProduct($id);

            // Define allowed status transitions per role
            $allowedStatuses = [
                'manufacturer' => [
                    'pending' => ['for_approval', 'inactive'],
                    'rejected' => ['for_approval', 'inactive'],
                    'inactive' => ['pending'],
                    'approved' => ['inactive']
                ],
                'regulator' => [
                    'for_approval' => ['approved', 'rejected']
                ]
            ];
            $currentProduct = $currentProduct['product'];
            Log::info('ksfbsbdf', [$currentProduct]);
            // Check if user has permission to update this product
            if ($user->role === 'manufacturer' && $currentProduct['userId'] !== $user->blockchain_id) {
                return response()->json([
                    'message' => 'You can only update your own products'
                ], 403);
            }

            Log::info('geresss');

            // Get allowed status transitions for the current user and product status
            $allowedTransitions = $allowedStatuses[$user->role][$currentProduct['status']] ?? [];
            Log::info('Allowed transitions:', [
                'user_role' => $user->role,
                'current_status' => $currentProduct['status'],
                'allowed_transitions' => $allowedTransitions
            ]);

            // Validate the request data first
            $rules = [
                'status' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($allowedTransitions) {
                        if (!in_array($value, $allowedTransitions)) {
                            $fail('Invalid status transition. Allowed transitions are: ' . implode(', ', $allowedTransitions));
                        }
                    }
                ],
                'notes' => 'required|string|min:2'
            ];
            Log::info('transition failed');

            $messages = [
                'status.required' => 'Status is required',
                'notes.required' => 'Notes are required',
                'notes.min' => 'Notes must be at least 10 characters'
            ];

            $validated = $request->validate($rules, $messages);
            Log::info('Validation passed:', ['validated_data' => $validated]);

            // Prepare update data
            $updateData = [
                'status' => $validated['status'],
                'notes' => $validated['notes'],
                'approvedBy' => $user->blockchain_id,
                'approvalDate' => now()->format('Y-m-d\TH:i:s\Z')
            ];
            Log::info('data', $updateData);

            // Add approval-specific data if user is regulator
            if ($user->role === 'regulator') {
                $updateData['approvedBy'] = $user->blockchain_id;
                $updateData['approvalDate'] = $updateData['approvalDate'];
                $updateData['notes'] = $validated['notes'];
            }

            // Update product status
            $result = $this->productApiService->updateProductStatus($id, $updateData);

            // Log the status change
            Log::info('Product status updated', [
                'product_id' => $id,
                'old_status' => $currentProduct['status'],
                'new_status' => $validated['status'],
                'updated_by' => $user->blockchain_id,
                'role' => $user->role
            ]);

          

            return response()->json([
                'message' => 'Product status updated successfully',
                'product' => $result
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Product status update failed:', [
                'error' => $e->getMessage(),
                'product_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to update product status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Helper method to get allowed status transitions
    private function getAllowedTransitions($userRole, $currentStatus)
    {
        $transitions = [
            'manufacturer' => [
                'pending' => ['for_approval', 'inactive'],
                'rejected' => ['for_approval', 'inactive'],
                'inactive' => ['pending'],
                'approved' => ['inactive']
            ],
            'regulator' => [
                'for_approval' => ['approved', 'rejected']
            ]
        ];

        return $transitions[$userRole][$currentStatus] ?? [];
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