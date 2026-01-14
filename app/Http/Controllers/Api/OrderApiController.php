<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderApiController extends Controller
{
    /**
     * Get orders by supplier CNPJ
     * Optional filters: status, date
     *
     * @param string $cnpj
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrdersBySupplierCnpj(string $cnpj, Request $request)
    {
        $user = $request->user();

        $supplier = Supplier::where('cnpj', $cnpj)->first();

        if (!$supplier) {
            return response()->json([
                'message' => 'Fornecedor não encontrado.',
            ], 404);
        }

        // Check if user has access to this supplier
        if ($user->type === 'vendedor') {
            $hasAccess = $user->suppliers()->where('suppliers.id', $supplier->id)->exists();
            if (!$hasAccess) {
                return response()->json([
                    'message' => 'Você não tem acesso aos pedidos deste fornecedor.',
                ], 403);
            }
        }

        $query = Order::where('supplier_id', $supplier->id)
            ->with(['items.product', 'user:id,name,email']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $orders = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'cnpj' => $supplier->cnpj,
            ],
            'orders' => $orders,
        ]);
    }

    /**
     * Get a specific order
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id, Request $request)
    {
        $user = $request->user();

        $order = Order::with(['items.product', 'supplier:id,name,cnpj', 'user:id,name,email'])
            ->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Pedido não encontrado.',
            ], 404);
        }

        // Check if user has access to this order
        if ($user->type === 'vendedor') {
            $hasAccess = $user->suppliers()->where('suppliers.id', $order->supplier_id)->exists();
            if (!$hasAccess) {
                return response()->json([
                    'message' => 'Você não tem acesso a este pedido.',
                ], 403);
            }
        }

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Create a new order
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Validate request
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'observation' => 'nullable|string|max:1000',
            'status' => 'nullable|in:Pendente,Concluído,Cancelado',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user has access to this supplier
        if ($user->type === 'vendedor') {
            $hasAccess = $user->suppliers()->where('suppliers.id', $request->supplier_id)->exists();
            if (!$hasAccess) {
                return response()->json([
                    'message' => 'Você não tem acesso a este fornecedor.',
                ], 403);
            }
        }

        // Verify all products belong to the supplier
        $productIds = collect($request->products)->pluck('id');
        $validProducts = DB::table('products')
            ->where('supplier_id', $request->supplier_id)
            ->whereIn('id', $productIds)
            ->count();

        if ($validProducts !== $productIds->count()) {
            return response()->json([
                'message' => 'Todos os produtos devem pertencer ao fornecedor selecionado.',
            ], 422);
        }

        // Create order
        DB::beginTransaction();
        try {
            // Calculate total amount
            $totalAmount = collect($request->products)->sum(function ($product) {
                return $product['quantity'] * $product['unit_price'];
            });

            $order = Order::create([
                'user_id' => $user->id,
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'total_amount' => $totalAmount,
                'observation' => $request->observation,
                'status' => $request->status ?? 'Pendente',
            ]);

            // Create order items
            foreach ($request->products as $product) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product['id'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'subtotal' => $product['quantity'] * $product['unit_price'],
                ]);
            }

            DB::commit();

            // Load relationships
            $order->load(['items.product', 'supplier:id,name,cnpj', 'user:id,name,email']);

            return response()->json([
                'message' => 'Pedido criado com sucesso.',
                'order' => $order,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar pedido.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing order
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(int $id, Request $request)
    {
        $user = $request->user();

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Pedido não encontrado.',
            ], 404);
        }

        // Check if user has access to this order
        if ($user->type === 'vendedor') {
            $hasAccess = $user->suppliers()->where('suppliers.id', $order->supplier_id)->exists();
            if (!$hasAccess) {
                return response()->json([
                    'message' => 'Você não tem acesso a este pedido.',
                ], 403);
            }
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|date',
            'observation' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:Pendente,Concluído,Cancelado',
            'products' => 'sometimes|array|min:1',
            'products.*.id' => 'required_with:products|exists:products,id',
            'products.*.quantity' => 'required_with:products|integer|min:1',
            'products.*.unit_price' => 'required_with:products|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Update order basic info
            $orderData = [];

            if ($request->has('date')) {
                $orderData['date'] = $request->date;
            }

            if ($request->has('observation')) {
                $orderData['observation'] = $request->observation;
            }

            if ($request->has('status')) {
                $orderData['status'] = $request->status;
            }

            // Update products if provided
            if ($request->has('products')) {
                // Verify all products belong to the supplier
                $productIds = collect($request->products)->pluck('id');
                $validProducts = DB::table('products')
                    ->where('supplier_id', $order->supplier_id)
                    ->whereIn('id', $productIds)
                    ->count();

                if ($validProducts !== $productIds->count()) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Todos os produtos devem pertencer ao fornecedor do pedido.',
                    ], 422);
                }

                // Calculate new total amount
                $totalAmount = collect($request->products)->sum(function ($product) {
                    return $product['quantity'] * $product['unit_price'];
                });

                $orderData['total_amount'] = $totalAmount;

                // Delete old items
                $order->items()->delete();

                // Create new items
                foreach ($request->products as $product) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product['id'],
                        'quantity' => $product['quantity'],
                        'unit_price' => $product['unit_price'],
                        'subtotal' => $product['quantity'] * $product['unit_price'],
                    ]);
                }
            }

            // Update order
            $order->update($orderData);

            DB::commit();

            // Load relationships
            $order->load(['items.product', 'supplier:id,name,cnpj', 'user:id,name,email']);

            return response()->json([
                'message' => 'Pedido atualizado com sucesso.',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao atualizar pedido.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an order
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id, Request $request)
    {
        $user = $request->user();

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Pedido não encontrado.',
            ], 404);
        }

        // Check if user has access to this order
        if ($user->type === 'vendedor') {
            $hasAccess = $user->suppliers()->where('suppliers.id', $order->supplier_id)->exists();
            if (!$hasAccess) {
                return response()->json([
                    'message' => 'Você não tem acesso a este pedido.',
                ], 403);
            }
        }

        DB::beginTransaction();
        try {
            // Delete order items first
            $order->items()->delete();

            // Delete order
            $order->delete();

            DB::commit();

            return response()->json([
                'message' => 'Pedido deletado com sucesso.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao deletar pedido.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all orders (for internal use)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Order::with(['items.product', 'supplier:id,name,cnpj', 'user:id,name,email']);

        // Filter by user's suppliers if vendedor
        if ($user->type === 'vendedor') {
            $supplierIds = $user->suppliers()->pluck('suppliers.id');
            $query->whereIn('supplier_id', $supplierIds);
        }

        $orders = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'orders' => $orders,
        ]);
    }
}

