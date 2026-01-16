<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers.
     */
    public function index(Request $request)
    {
        $query = Supplier::with('users');

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $suppliers = $query->latest()->paginate(10)->appends($request->only(['search']));

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create()
    {
        $vendedores = User::where('type', 'vendedor')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Suppliers/Create', [
            'vendedores' => $vendedores,
        ]);
    }

    /**
     * Store a newly created supplier in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|cnpj|unique:suppliers,cnpj',
            'cep' => 'required|string|max:9',
            'address' => 'required|string|max:500',
            'status' => 'required|boolean',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $supplier = Supplier::create([
            'name' => $validated['name'],
            'cnpj' => $validated['cnpj'],
            'cep' => $validated['cep'],
            'address' => $validated['address'],
            'status' => $validated['status'],
        ]);

        // Sincronizar vendedores
        if (isset($validated['user_ids'])) {
            $supplier->users()->sync($validated['user_ids']);
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Fornecedor cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified supplier.
     */
    public function edit(Supplier $supplier)
    {
        $vendedores = User::where('type', 'vendedor')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        $supplier->load('users');

        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier,
            'vendedores' => $vendedores,
        ]);
    }

    /**
     * Update the specified supplier in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cnpj' => 'required|cnpj|unique:suppliers,cnpj,' . $supplier->id,
            'cep' => 'required|string|max:9',
            'address' => 'required|string|max:500',
            'status' => 'required|boolean',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $supplier->update([
            'name' => $validated['name'],
            'cnpj' => $validated['cnpj'],
            'cep' => $validated['cep'],
            'address' => $validated['address'],
            'status' => $validated['status'],
        ]);

        // Sincronizar vendedores
        if (isset($validated['user_ids'])) {
            $supplier->users()->sync($validated['user_ids']);
        } else {
            $supplier->users()->sync([]);
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'Fornecedor atualizado com sucesso!');
    }

    /**
     * Remove the specified supplier from storage.
     */
    public function destroy(Supplier $supplier)
    {
        // Verificar se existem pedidos relacionados a este fornecedor
        if ($supplier->orders()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Não foi possível apagar pois existem pedidos relacionados a esse Fornecedor.');
        }

        // Verificar se existem produtos relacionados a este fornecedor
        if ($supplier->products()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Não foi possível apagar pois existem produtos relacionados a esse Fornecedor.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Fornecedor excluído com sucesso!');
    }

    /**
     * Fetch address from ViaCEP API
     */
    public function fetchAddress(Request $request)
    {
        $request->validate([
            'cep' => 'required|string|max:9',
        ]);

        $cep = preg_replace('/[^0-9]/', '', $request->cep);

        try {
            $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

            if ($response->successful() && !isset($response->json()['erro'])) {
                $data = $response->json();

                return response()->json([
                    'success' => true,
                    'address' => [
                        'logradouro' => $data['logradouro'],
                        'bairro' => $data['bairro'],
                        'cidade' => $data['localidade'],
                        'estado' => $data['uf'],
                        'full_address' => "{$data['logradouro']}, {$data['bairro']}, {$data['localidade']} - {$data['uf']}",
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'CEP não encontrado.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar CEP.',
            ], 500);
        }
    }
}

