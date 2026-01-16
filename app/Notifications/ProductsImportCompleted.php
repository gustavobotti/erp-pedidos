<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductsImportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $importedCount;
    protected $errors;

    /**
     * Create a new notification instance.
     */
    public function __construct($importedCount, $errors = [])
    {
        $this->importedCount = $importedCount;
        $this->errors = $errors;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Importação de Produtos Concluída')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('A importação de produtos foi concluída.');

        if ($this->importedCount > 0) {
            $message->line("✅ **{$this->importedCount} produto(s)** importado(s) com sucesso!");
        }

        if (!empty($this->errors)) {
            $message->line('⚠️ Alguns erros foram encontrados:')
                ->line('---');

            $errorCount = count($this->errors);
            $displayErrors = array_slice($this->errors, 0, 10);

            foreach ($displayErrors as $error) {
                $message->line('• ' . $error);
            }

            if ($errorCount > 10) {
                $message->line("... e mais " . ($errorCount - 10) . " erro(s).");
            }
        }

        if ($this->importedCount === 0 && empty($this->errors)) {
            $message->line('Nenhum produto foi importado. Verifique o formato do arquivo.');
        }

        $message->line('Obrigado por usar nosso sistema!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'imported_count' => $this->importedCount,
            'errors_count' => count($this->errors),
        ];
    }
}

