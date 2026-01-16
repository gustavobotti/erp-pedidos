<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductsImportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $userId;
    protected $supplierId;

    /**
     * Create a new job instance.
     */
    public function __construct($filePath, $userId, $supplierId = null)
    {
        $this->filePath = $filePath;
        $this->userId = $userId;
        $this->supplierId = $supplierId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = User::find($this->userId);
        $imported = 0;
        $errors = [];

        try {
            // Read CSV file
            $csvContent = Storage::get($this->filePath);
            $lines = explode("\n", $csvContent);

            // Remove empty lines
            $lines = array_filter($lines, function($line) {
                return !empty(trim($line));
            });
            $lines = array_values($lines); // Reindex array

            if (empty($lines)) {
                throw new \Exception("Arquivo CSV está vazio");
            }

            // Check if first line is a header
            $firstLine = str_getcsv(trim($lines[0]));
            $isHeader = false;

            // Detect header by checking if it contains expected column names
            if (count($firstLine) >= 4) {
                $firstLineStr = strtolower(implode(',', $firstLine));
                if (strpos($firstLineStr, 'referencia') !== false ||
                    strpos($firstLineStr, 'nome') !== false ||
                    strpos($firstLineStr, 'preco') !== false) {
                    $isHeader = true;
                    array_shift($lines); // Remove header
                    Log::info('CSV header detected and skipped');
                }
            }

            if (!$isHeader) {
                Log::info('No header detected, processing all lines as data');
            }

            foreach ($lines as $index => $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                // Parse CSV line
                $data = str_getcsv($line);

                if (count($data) < 4) {
                    $lineNumber = $isHeader ? ($index + 2) : ($index + 1);
                    $errors[] = "Linha " . $lineNumber . ": Formato inválido (encontradas " . count($data) . " colunas, esperadas 4)";
                    continue;
                }

                try {
                    // Determine supplier_id
                    $supplierId = $this->supplierId;

                    // Validate and create product
                    Product::create([
                        'supplier_id' => $supplierId,
                        'reference' => trim($data[0]),
                        'name' => trim($data[1]),
                        'color' => trim($data[2]),
                        'price' => floatval(str_replace(',', '.', trim($data[3]))),
                    ]);

                    $imported++;
                } catch (\Exception $e) {
                    $lineNumber = $isHeader ? ($index + 2) : ($index + 1);
                    $errors[] = "Linha " . $lineNumber . ": " . $e->getMessage();
                    Log::error("Error importing product line " . $lineNumber, [
                        'error' => $e->getMessage(),
                        'data' => $data
                    ]);
                }
            }

            // Delete the temporary file
            Storage::delete($this->filePath);

            // Send notification
            $user->notify(new ProductsImportCompleted($imported, $errors));

        } catch (\Exception $e) {
            Log::error("Error in ImportProductsJob", [
                'error' => $e->getMessage(),
                'file' => $this->filePath
            ]);

            // Send error notification
            $user->notify(new ProductsImportCompleted(0, ["Erro ao processar arquivo: " . $e->getMessage()]));

            // Clean up file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }
}

