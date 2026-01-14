<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Supplier;
use App\Services\ProductCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    protected $productCacheService;

    public function __construct(ProductCacheService $productCacheService)
    {
        $this->productCacheService = $productCacheService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['supplier', 'user']);

        // Filter out orders from inactive suppliers
        $query->whereHas('supplier', function ($q) {
            $q->where('status', true);
        });

        // Filter by supplier if user is vendedor
        if (auth()->user()->isVendedor()) {
            $supplierIds = auth()->user()->suppliers()->where('status', true)->pluck('suppliers.id')->toArray();

            if (empty($supplierIds)) {
                $query->whereRaw('1 = 0'); // Return empty
            } else {
                $query->whereIn('supplier_id', $supplierIds);
            }
        }

        // Filter by supplier from request
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $orders = $query->latest('date')->paginate(10);

        // Get suppliers for filter
        $suppliers = auth()->user()->isAdmin()
            ? Supplier::where('status', true)->get()
            : auth()->user()->suppliers()->where('status', true)->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'suppliers' => $suppliers,
            'filters' => $request->only(['supplier_id', 'status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $suppliers = auth()->user()->isAdmin()
            ? Supplier::where('status', true)->get()
            : auth()->user()->suppliers()->where('status', true)->get();

        return Inertia::render('Orders/Create', [
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'observation' => 'nullable|string|max:1000',
            'status' => 'required|in:Pendente,Concluído,Cancelado',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Check if supplier is active
        $supplier = Supplier::find($validated['supplier_id']);
        if (!$supplier || !$supplier->status) {
            return back()->withErrors(['supplier_id' => 'Não é possível criar pedidos para fornecedores inativos.']);
        }

        // Check if user has access to the supplier
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $validated['supplier_id'])->exists();
            if (!$hasAccess) {
                return back()->withErrors(['supplier_id' => 'Você não tem acesso a este fornecedor.']);
            }
        }

        // Verify all products belong to the selected supplier
        $productIds = collect($validated['products'])->pluck('product_id')->toArray();
        $invalidProducts = \App\Models\Product::whereIn('id', $productIds)
            ->where('supplier_id', '!=', $validated['supplier_id'])
            ->exists();

        if ($invalidProducts) {
            return back()->withErrors(['products' => 'Todos os produtos devem pertencer ao fornecedor selecionado.']);
        }

        DB::beginTransaction();

        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($validated['products'] as $product) {
                $totalAmount += $product['quantity'] * $product['unit_price'];
            }

            // Create order
            $order = Order::create([
                'user_id' => auth()->id(),
                'supplier_id' => $validated['supplier_id'],
                'date' => $validated['date'],
                'total_amount' => $totalAmount,
                'observation' => $validated['observation'],
                'status' => $validated['status'],
            ]);

            // Create order items
            foreach ($validated['products'] as $product) {
                $subtotal = $product['quantity'] * $product['unit_price'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product['product_id'],
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'subtotal' => $subtotal,
                ]);
            }

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Pedido cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao cadastrar pedido: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        // Check if user has access to the order
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $order->supplier_id)->exists();
            if (!$hasAccess) {
                abort(403, 'Você não tem acesso a este pedido.');
            }
        }

        $order->load(['supplier', 'user', 'items.product']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order)
    {
        // Check if user has access to the order
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $order->supplier_id)->exists();
            if (!$hasAccess) {
                abort(403, 'Você não tem acesso a este pedido.');
            }
        }

        $order->load(['supplier', 'items.product']);

        $suppliers = auth()->user()->isAdmin()
            ? Supplier::where('status', true)->get()
            : auth()->user()->suppliers()->where('status', true)->get();

        // Get products from cache for the order's supplier
        $products = $this->productCacheService->getProductsBySupplier($order->supplier_id);

        return Inertia::render('Orders/Edit', [
            'order' => $order,
            'suppliers' => $suppliers,
            'initialProducts' => $products,
        ]);
    }

    /**
     * Update the specified order in storage.
     */
    public function update(Request $request, Order $order)
    {
        // Check if user has access to the order
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $order->supplier_id)->exists();
            if (!$hasAccess) {
                abort(403, 'Você não tem acesso a este pedido.');
            }
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'observation' => 'nullable|string|max:1000',
            'status' => 'required|in:Pendente,Concluído,Cancelado',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ]);

        // Check if supplier is active
        $supplier = Supplier::find($validated['supplier_id']);
        if (!$supplier || !$supplier->status) {
            return back()->withErrors(['supplier_id' => 'Não é possível atualizar pedidos para fornecedores inativos.']);
        }

        // Check if user has access to the supplier
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $validated['supplier_id'])->exists();
            if (!$hasAccess) {
                return back()->withErrors(['supplier_id' => 'Você não tem acesso a este fornecedor.']);
            }
        }

        // Verify all products belong to the selected supplier
        $productIds = collect($validated['products'])->pluck('product_id')->toArray();
        $invalidProducts = \App\Models\Product::whereIn('id', $productIds)
            ->where('supplier_id', '!=', $validated['supplier_id'])
            ->exists();

        if ($invalidProducts) {
            return back()->withErrors(['products' => 'Todos os produtos devem pertencer ao fornecedor selecionado.']);
        }

        DB::beginTransaction();

        try {
            // Calculate total amount
            $totalAmount = 0;
            foreach ($validated['products'] as $product) {
                $totalAmount += $product['quantity'] * $product['unit_price'];
            }

            // Update order
            $order->update([
                'supplier_id' => $validated['supplier_id'],
                'date' => $validated['date'],
                'total_amount' => $totalAmount,
                'observation' => $validated['observation'],
                'status' => $validated['status'],
            ]);

            // Delete old order items
            $order->items()->delete();

            // Create new order items
            foreach ($validated['products'] as $product) {
                $subtotal = $product['quantity'] * $product['unit_price'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product['product_id'],
                    'unit_price' => $product['unit_price'],
                    'quantity' => $product['quantity'],
                    'subtotal' => $subtotal,
                ]);
            }

            DB::commit();

            return redirect()->route('orders.index')
                ->with('success', 'Pedido atualizado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erro ao atualizar pedido: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        // Check if user has access to the order
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $order->supplier_id)->exists();
            if (!$hasAccess) {
                abort(403, 'Você não tem acesso a este pedido.');
            }
        }

        $order->items()->delete();
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Pedido excluído com sucesso!');
    }

    /**
     * Get products by supplier from cache (API endpoint)
     */
    public function getProductsBySupplier(Request $request, $supplierId)
    {
        // Check if user has access to the supplier
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $supplierId)->exists();
            if (!$hasAccess) {
                return response()->json(['error' => 'Acesso negado a este fornecedor.'], 403);
            }
        }

        $products = $this->productCacheService->getProductsBySupplier($supplierId);

        return response()->json($products);
    }
}

