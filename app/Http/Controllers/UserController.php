<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->only(['search']));

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $suppliers = \App\Models\Supplier::where('status', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Users/Create', [
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'type' => 'required|in:vendedor,admin',
            'status' => 'required|boolean',
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'status' => $request->status,
        ]);

        // Sincronizar fornecedores apenas se for vendedor
        if ($request->type === 'vendedor' && $request->has('supplier_ids')) {
            $user->suppliers()->sync($request->supplier_ids);
        }

        return redirect()->route('users.index')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $suppliers = \App\Models\Supplier::where('status', true)
            ->orderBy('name')
            ->get();

        $user->load('suppliers');

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,'.$user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'type' => 'required|in:vendedor,admin',
            'status' => 'required|boolean',
            'supplier_ids' => 'nullable|array',
            'supplier_ids.*' => 'exists:suppliers,id',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'type' => $request->type,
            'status' => $request->status,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Sincronizar fornecedores apenas se for vendedor
        if ($request->type === 'vendedor') {
            if ($request->has('supplier_ids')) {
                $user->suppliers()->sync($request->supplier_ids);
            } else {
                $user->suppliers()->sync([]);
            }
        } else {
            // Se mudou de vendedor para admin, remover vínculos
            $user->suppliers()->sync([]);
        }

        return redirect()->route('users.index')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Impedir que o usuário delete a si mesmo
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Você não pode deletar seu próprio usuário.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Usuário deletado com sucesso!');
    }
}

