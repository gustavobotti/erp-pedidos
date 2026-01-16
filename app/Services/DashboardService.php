<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get dashboard data for the last 30 days
     * Data is cached in Redis for 1 hour
     */
    public function getDashboardData()
    {
        return Cache::remember('dashboard:last_30_days', 3600, function () {
            $startDate = Carbon::now()->subDays(30);
            $endDate = Carbon::now();

            return [
                'top_suppliers' => $this->getTopSuppliers($startDate, $endDate),
                'total_orders' => $this->getTotalOrders($startDate, $endDate),
                'total_revenue' => $this->getTotalRevenue($startDate, $endDate),
                'orders_by_status' => $this->getOrdersByStatus($startDate, $endDate),
                'recent_orders' => $this->getRecentOrders(),
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ],
            ];
        });
    }

    /**
     * Get top 10 suppliers by sales volume
     */
    private function getTopSuppliers($startDate, $endDate)
    {
        return Order::select(
                'suppliers.id',
                'suppliers.name',
                DB::raw('COUNT(orders.id) as total_orders'),
                DB::raw('SUM(orders.total_amount) as total_sales')
            )
            ->join('suppliers', 'orders.supplier_id', '=', 'suppliers.id')
            ->whereBetween('orders.date', [$startDate, $endDate])
            ->whereIn('orders.status', ['Pendente', 'Concluído'])
            ->groupBy('suppliers.id', 'suppliers.name')
            ->orderBy('total_sales', 'DESC')
            ->limit(10)
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'total_orders' => $supplier->total_orders,
                    'total_sales' => (float) $supplier->total_sales,
                ];
            });
    }

    /**
     * Get total orders count
     */
    private function getTotalOrders($startDate, $endDate)
    {
        return Order::whereBetween('date', [$startDate, $endDate])->count();
    }

    /**
     * Get total revenue
     */
    private function getTotalRevenue($startDate, $endDate)
    {
        return (float) Order::whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['Pendente', 'Concluído'])
            ->sum('total_amount');
    }

    /**
     * Get orders grouped by status
     */
    private function getOrdersByStatus($startDate, $endDate)
    {
        return Order::select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'total' => (float) $item->total,
                ];
            });
    }

    /**
     * Get recent orders (last 5)
     */
    private function getRecentOrders()
    {
        return Order::with(['supplier', 'user'])
            ->latest('date')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'date' => $order->date->format('d/m/Y'),
                    'supplier' => $order->supplier->name,
                    'user' => $order->user->name,
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                ];
            });
    }

    /**
     * Clear dashboard cache
     */
    public function clearCache()
    {
        Cache::forget('dashboard:last_30_days');
    }
}

