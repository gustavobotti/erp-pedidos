<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with factories.
     * This seeder creates much more data than the basic one.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting database seeding with factories...');

        // =====================================================================
        // 1. CRIAR ADMIN
        // =====================================================================
        $this->command->info('Creating admin user...');

        $admin = User::factory()->admin()->create([
            'name' => 'Administrador',
            'email' => 'admin@erp.com',
            'password' => Hash::make('password'),
        ]);

        // =====================================================================
        // 2. CRIAR VENDEDORES (50 vendedores ativos + 5 inativos)
        // =====================================================================
        $this->command->info('Creating 55 sellers (50 active + 5 inactive)...');

        // 3 vendedores principais (com emails conhecidos)
        $vendedor1 = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@erp.com',
            'password' => Hash::make('password'),
            'type' => 'vendedor',
        ]);

        $vendedor2 = User::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@erp.com',
            'password' => Hash::make('password'),
            'type' => 'vendedor',
        ]);

        $vendedor3 = User::factory()->create([
            'name' => 'Pedro Costa',
            'email' => 'pedro@erp.com',
            'password' => Hash::make('password'),
            'type' => 'vendedor',
        ]);

        // 47 vendedores ativos aleatórios
        $vendedoresAtivos = User::factory()->count(47)->create([
            'type' => 'vendedor',
            'status' => 1,
        ]);

        // 5 vendedores inativos
        $vendedoresInativos = User::factory()->count(5)->inactive()->create([
            'type' => 'vendedor',
        ]);

        // Coletar todos os vendedores
        $todosVendedores = collect([$vendedor1, $vendedor2, $vendedor3])
            ->merge($vendedoresAtivos)
            ->merge($vendedoresInativos);

        // =====================================================================
        // 3. CRIAR FORNECEDORES (100 fornecedores ativos + 20 inativos)
        // =====================================================================
        $this->command->info('Creating 120 suppliers (100 active + 20 inactive)...');

        // 5 fornecedores principais
        $fornecedor1 = Supplier::factory()->active()->create([
            'name' => 'TechSupply Ltda',
            'cnpj' => '97150072000157',
        ]);

        $fornecedor2 = Supplier::factory()->active()->create([
            'name' => 'InfoParts Distribuidora',
            'cnpj' => '45200898000180',
        ]);

        $fornecedor3 = Supplier::factory()->active()->create([
            'name' => 'Digital Store',
            'cnpj' => '09391052000100',
        ]);

        $fornecedor4 = Supplier::factory()->active()->create([
            'name' => 'Mega Componentes',
            'cnpj' => '13817179000116',
        ]);

        $fornecedor5 = Supplier::factory()->inactive()->create([
            'name' => 'Prime Electronics',
            'cnpj' => '47752943000134',
        ]);

        // 95 fornecedores ativos aleatórios
        $fornecedoresAtivos = Supplier::factory()->count(95)->active()->create();

        // 19 fornecedores inativos aleatórios
        $fornecedoresInativos = Supplier::factory()->count(19)->inactive()->create();

        // Coletar todos os fornecedores
        $todosFornecedores = collect([$fornecedor1, $fornecedor2, $fornecedor3, $fornecedor4, $fornecedor5])
            ->merge($fornecedoresAtivos)
            ->merge($fornecedoresInativos);

        // =====================================================================
        // 4. CRIAR RELACIONAMENTOS VENDEDOR-FORNECEDOR
        // =====================================================================
        $this->command->info('Creating supplier-seller relationships...');

        // Cada vendedor tem acesso a entre 5 e 20 fornecedores aleatórios
        $fornecedoresAtivosList = $todosFornecedores->where('status', 1);

        foreach ($todosVendedores as $vendedor) {
            // Vendedores ativos recebem mais fornecedores
            $numFornecedores = $vendedor->status === 1
                ? rand(5, 20)
                : rand(2, 8);

            $fornecedoresAleatorios = $fornecedoresAtivosList
                ->random(min($numFornecedores, $fornecedoresAtivosList->count()))
                ->pluck('id')
                ->toArray();

            $vendedor->suppliers()->attach($fornecedoresAleatorios);
        }

        // =====================================================================
        // 5. CRIAR PRODUTOS (1000 produtos)
        // =====================================================================
        $this->command->info('Creating 1000 products...');

        // Distribuir produtos entre todos os fornecedores
        $produtos = collect();

        foreach ($todosFornecedores as $fornecedor) {
            // Cada fornecedor tem entre 5 e 15 produtos
            $numProdutos = rand(5, 15);

            $produtosFornecedor = Product::factory()->count($numProdutos)->create([
                'supplier_id' => $fornecedor->id,
            ]);

            $produtos = $produtos->merge($produtosFornecedor);
        }

        // =====================================================================
        // 6. CRIAR PEDIDOS (500 pedidos com itens)
        // =====================================================================
        $this->command->info('Creating 500 orders with items...');

        $statusDistribution = [
            'Pendente' => 200,
            'Concluído' => 250,
            'Cancelado' => 50,
        ];

        foreach ($statusDistribution as $status => $quantidade) {
            for ($i = 0; $i < $quantidade; $i++) {
                // Selecionar um vendedor ativo aleatório
                $vendedor = $todosVendedores->where('status', 1)->random();

                // Selecionar um fornecedor que o vendedor tem acesso
                $fornecedoresDoVendedor = $vendedor->suppliers;

                if ($fornecedoresDoVendedor->isEmpty()) {
                    continue; // Pular se o vendedor não tem fornecedores
                }

                $fornecedor = $fornecedoresDoVendedor->random();

                // Criar o pedido
                $pedido = Order::factory()->create([
                    'user_id' => $vendedor->id,
                    'supplier_id' => $fornecedor->id,
                    'status' => $status,
                    'date' => fake()->dateTimeBetween('-6 months', 'now'),
                ]);

                // Adicionar entre 1 e 5 itens ao pedido
                $numItens = rand(1, 5);
                $produtosFornecedor = $produtos->where('supplier_id', $fornecedor->id);

                if ($produtosFornecedor->isEmpty()) {
                    continue; // Pular se não há produtos para este fornecedor
                }

                $totalAmount = 0;

                for ($j = 0; $j < $numItens; $j++) {
                    $produto = $produtosFornecedor->random();
                    $quantity = rand(1, 10);
                    $subtotal = $produto->price * $quantity;
                    $totalAmount += $subtotal;

                    OrderItem::create([
                        'order_id' => $pedido->id,
                        'product_id' => $produto->id,
                        'unit_price' => $produto->price,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal,
                    ]);
                }

                // Atualizar o total do pedido
                $pedido->update(['total_amount' => $totalAmount]);
            }
        }

        // =====================================================================
        // RESUMO FINAL
        // =====================================================================
        $this->command->newLine();
        $this->command->info('✅ Database seeded successfully with factories!');
        $this->command->newLine();
        $this->command->table(
            ['Entity', 'Quantity'],
            [
                ['Admin', '1'],
                ['Sellers (Active)', '50'],
                ['Sellers (Inactive)', '5'],
                ['Suppliers (Active)', '100'],
                ['Suppliers (Inactive)', '20'],
                ['Products', $produtos->count()],
                ['Orders (Pending)', '200'],
                ['Orders (Completed)', '250'],
                ['Orders (Canceled)', '50'],
            ]
        );

        $this->command->newLine();
        $this->command->info('📧 Test accounts:');
        $this->command->info('   Admin: admin@erp.com');
        $this->command->info('   Seller 1: joao@erp.com');
        $this->command->info('   Seller 2: maria@erp.com');
        $this->command->info('   Seller 3: pedro@erp.com');
        $this->command->info('   Password: password');
        $this->command->newLine();
    }
}

