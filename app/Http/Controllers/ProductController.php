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
            return view('products.index', compact('products'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
            // Validate basic product information
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'manufacture_date' => 'required|date',
                'expiry_date' => 'nullable|date|after:manufacture_date',
                'price' => 'required|numeric|min:0',
                'status' => 'required|in:active,inactive',
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
                'userId' => 'user1',
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
                        //'value' => $req['value'] // This is now the unit string
                        'value' => 3.0
                    ];
                }, $requirements),
                'notes' => $validated['notes'] ?? '',

            ];
            Log::info($productData);

            // Create product through API service
            $product = $this->productApiService->createProduct($productData);

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully');

        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (Exception $e) {
            logger()->error('Product creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create product. ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:created,waiting_approval,approved,active,inactive',
                'notes' => 'required|string|max:500'
            ]);
    
            $response = $this->productApiService->updateProductStatus($id, $validated['status'], $validated['notes']);
    
            return response()->json(['message' => 'Product status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
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