<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index(Request $request)
    {
        $query = Product::with('supplier');

        // Filter by supplier if user is vendedor
        if (auth()->user()->isVendedor()) {
            $supplierIds = auth()->user()->suppliers()->pluck('suppliers.id')->toArray();

            // Se o vendedor não tem fornecedores vinculados, não mostra nenhum produto
            if (empty($supplierIds)) {
                $query->whereRaw('1 = 0'); // Retorna vazio
            } else {
                $query->whereIn('supplier_id', $supplierIds);
            }
        }

        // Filter by supplier from request
        if ($request->has('supplier_id') && $request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $products = $query->latest()->paginate(10);

        // Get suppliers for filter
        $suppliers = auth()->user()->isAdmin()
            ? Supplier::where('status', true)->get()
            : auth()->user()->suppliers()->where('status', true)->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'suppliers' => $suppliers,
            'filters' => $request->only(['supplier_id']),
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $suppliers = auth()->user()->isAdmin()
            ? Supplier::where('status', true)->get()
            : auth()->user()->suppliers()->where('status', true)->get();

        return Inertia::render('Products/Create', [
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'reference' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
        ]);

        // Check if user has access to the supplier
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $validated['supplier_id'])->exists();
            if (!$hasAccess) {
                return back()->withErrors(['supplier_id' => 'Você não tem acesso a este fornecedor.']);
            }
        }

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produto cadastrado com sucesso!');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        // Check if user has access to the product
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $product->supplier_id)->exists();
            if (!$hasAccess) {
                abort(403, 'Você não tem acesso a este produto.');
            }
        }

        $product->load('supplier');

        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Check if user has access to the product
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $product->supplier_id)->exists();
            if (!$hasAccess) {
                abort(403, 'Você não tem acesso a este produto.');
            }
        }

        $suppliers = auth()->user()->isAdmin()
            ? Supplier::where('status', true)->get()
            : auth()->user()->suppliers()->where('status', true)->get();

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'reference' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
        ]);

        // Check if user has access to the supplier
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $validated['supplier_id'])->exists();
            if (!$hasAccess) {
                return back()->withErrors(['supplier_id' => 'Você não tem acesso a este fornecedor.']);
            }
        }

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Check if user has access to the product
        if (auth()->user()->isVendedor()) {
            $hasAccess = auth()->user()->suppliers()->where('suppliers.id', $product->supplier_id)->exists();
            if (!$hasAccess) {
                abort(403, 'Você não tem acesso a este produto.');
            }
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produto excluído com sucesso!');
    }
}

