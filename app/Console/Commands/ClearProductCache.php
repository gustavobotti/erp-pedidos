<?php

namespace App\Console\Commands;

use App\Services\ProductCacheService;
use Illuminate\Console\Command;

class ClearProductCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-products {supplier_id? : ID do fornecedor específico}
                            {--all : Limpar cache de todos os fornecedores}
                            {--stats : Mostrar estatísticas do cache}
                            {--warmup : Pré-carregar cache após limpar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gerenciar cache de produtos por fornecedor';

    protected $cacheService;

    public function __construct(ProductCacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Show cache statistics
        if ($this->option('stats')) {
            $this->showStats();
            return 0;
        }

        // Clear all caches
        if ($this->option('all')) {
            $this->info('Limpando cache de todos os fornecedores...');
            $this->cacheService->clearAllProductCaches();
            $this->info('✅ Cache de todos os fornecedores limpo com sucesso!');

            if ($this->option('warmup')) {
                $this->warmupAllCaches();
            }

            return 0;
        }

        // Clear specific supplier cache
        $supplierId = $this->argument('supplier_id');

        if ($supplierId) {
            $this->info("Limpando cache do fornecedor {$supplierId}...");
            $this->cacheService->clearSupplierCache($supplierId);
            $this->info("✅ Cache do fornecedor {$supplierId} limpo com sucesso!");

            if ($this->option('warmup')) {
                $this->info("Pré-carregando cache...");
                $this->cacheService->warmUpCache($supplierId);
                $this->info("✅ Cache pré-carregado!");
            }

            return 0;
        }

        // No option provided
        $this->error('❌ Forneça um supplier_id ou use --all para limpar todos os caches.');
        $this->info('Exemplos:');
        $this->line('  php artisan cache:clear-products 1');
        $this->line('  php artisan cache:clear-products --all');
        $this->line('  php artisan cache:clear-products --all --warmup');
        $this->line('  php artisan cache:clear-products --stats');

        return 1;
    }

    /**
     * Show cache statistics
     */
    protected function showStats()
    {
        $stats = $this->cacheService->getCacheStats();

        $this->info('📊 Estatísticas do Cache de Produtos');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total de Fornecedores', $stats['total_suppliers']],
                ['Fornecedores em Cache', $stats['cached_suppliers']],
                ['% em Cache', $stats['total_suppliers'] > 0
                    ? round(($stats['cached_suppliers'] / $stats['total_suppliers']) * 100, 2) . '%'
                    : '0%'
                ],
            ]
        );

        if (!empty($stats['cache_keys'])) {
            $this->newLine();
            $this->info('🔑 Chaves em Cache:');
            foreach ($stats['cache_keys'] as $key) {
                $this->line("  • {$key}");
            }
        }
    }

    /**
     * Warm up all caches
     */
    protected function warmupAllCaches()
    {
        $this->info('🔥 Pré-carregando cache de todos os fornecedores...');

        $suppliers = \App\Models\Supplier::pluck('id');
        $bar = $this->output->createProgressBar($suppliers->count());
        $bar->start();

        foreach ($suppliers as $supplierId) {
            $this->cacheService->warmUpCache($supplierId);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('✅ Cache de todos os fornecedores pré-carregado!');
    }
}
