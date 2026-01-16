<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyOrdersReport extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $statistics
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Relatório Diário de Pedidos - Últimos 7 Dias')
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line('Segue o resumo dos pedidos dos últimos 7 dias:')
            ->line('**Período:** ' . $this->statistics['period']['start'] . ' a ' . $this->statistics['period']['end'])
            ->line('---')
            ->line('📊 **Total de Pedidos:** ' . $this->statistics['total_orders'])
            ->line('💰 **Valor Total:** R$ ' . number_format($this->statistics['total_amount'], 2, ',', '.'))
            ->line('---');

        // Adicionar estatísticas por status
        foreach ($this->statistics['by_status'] as $status => $data) {
            $emoji = match($status) {
                'Pendente' => '⏳',
                'Concluído' => '✅',
                'Cancelado' => '❌',
                default => '📦',
            };

            $message->line("**{$emoji} {$status}:**")
                ->line("- Quantidade: {$data['count']} pedido(s)")
                ->line("- Valor Total: R$ " . number_format($data['amount'], 2, ',', '.'));

            // Listar pedidos se houver
            if ($data['count'] > 0 && $data['count'] <= 5) {
                $message->line('');
                foreach ($data['orders'] as $order) {
                    $message->line("  • Pedido #{$order['id']} - {$order['supplier']} - R$ " .
                        number_format($order['total_amount'], 2, ',', '.') .
                        " (" . date('d/m/Y', strtotime($order['date'])) . ")");
                }
            } elseif ($data['count'] > 5) {
                $message->line("  (Mostrando primeiros 5 de {$data['count']} pedidos)");
                $message->line('');
                foreach (array_slice($data['orders'], 0, 5) as $order) {
                    $message->line("  • Pedido #{$order['id']} - {$order['supplier']} - R$ " .
                        number_format($order['total_amount'], 2, ',', '.') .
                        " (" . date('d/m/Y', strtotime($order['date'])) . ")");
                }
            }

            $message->line('---');
        }

        $message->line('Este é um relatório automático gerado pelo sistema.')
            ->salutation('Equipe ERP Pedidos');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'statistics' => $this->statistics,
        ];
    }
}

