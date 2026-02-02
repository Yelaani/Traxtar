<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Livewire\Component;
use Carbon\Carbon;

class AnalyticsDashboard extends Component
{
    public $period = 'daily'; // 'daily', 'weekly', 'monthly'

    public function mount()
    {
        // Initialize with default period
    }

    public function updatedPeriod()
    {
        // Dispatch event to update charts when period changes
        $this->dispatch('period-updated');
    }

    public function getMetricsProperty()
    {
        $dateRange = $this->getDateRange();
        
        return [
            'total_orders' => $this->getTotalOrders($dateRange),
            'total_revenue' => $this->getTotalRevenue($dateRange),
            'new_users' => $this->getNewUsers($dateRange),
        ];
    }

    public function getRevenueDataProperty()
    {
        $dateRange = $this->getDateRange();
        
        return $this->getRevenueByPeriod($dateRange);
    }

    public function getOrdersDataProperty()
    {
        $dateRange = $this->getDateRange();
        
        return $this->getOrdersByPeriod($dateRange);
    }

    public function getBestSellingProductsProperty()
    {
        $dateRange = $this->getDateRange();
        
        return $this->getBestSellingProducts($dateRange);
    }

    private function getDateRange()
    {
        $endDate = Carbon::now();
        
        switch ($this->period) {
            case 'daily':
                $startDate = $endDate->copy()->subDays(30);
                break;
            case 'weekly':
                $startDate = $endDate->copy()->subWeeks(12);
                break;
            case 'monthly':
                $startDate = $endDate->copy()->subMonths(12);
                break;
            default:
                $startDate = $endDate->copy()->subDays(30);
        }
        
        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    private function getTotalOrders($dateRange)
    {
        return Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();
    }

    private function getTotalRevenue($dateRange)
    {
        return Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->sum('total');
    }

    private function getNewUsers($dateRange)
    {
        return User::where('role', 'customer')
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->count();
    }

    private function getRevenueByPeriod($dateRange)
    {
        $query = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        switch ($this->period) {
            case 'daily':
                $data = $query->selectRaw('DATE(created_at) as period, SUM(total) as revenue')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
            case 'weekly':
                $data = $query->selectRaw('YEARWEEK(created_at) as period, SUM(total) as revenue')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
            case 'monthly':
                $data = $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, SUM(total) as revenue')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
            default:
                $data = collect();
        }
        
        return $data->map(function ($item) {
            return [
                'period' => $this->formatPeriod($item->period),
                'revenue' => (float) $item->revenue,
            ];
        })->toArray();
    }

    private function getOrdersByPeriod($dateRange)
    {
        $query = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        switch ($this->period) {
            case 'daily':
                $data = $query->selectRaw('DATE(created_at) as period, COUNT(*) as count')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
            case 'weekly':
                $data = $query->selectRaw('YEARWEEK(created_at) as period, COUNT(*) as count')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
            case 'monthly':
                $data = $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COUNT(*) as count')
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();
                break;
            default:
                $data = collect();
        }
        
        return $data->map(function ($item) {
            return [
                'period' => $this->formatPeriod($item->period),
                'count' => (int) $item->count,
            ];
        })->toArray();
    }

    private function getBestSellingProducts($dateRange)
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('order_items.product_id, order_items.product_name, SUM(order_items.qty) as total_qty, SUM(order_items.price * order_items.qty) as total_revenue')
            ->groupBy('order_items.product_id', 'order_items.product_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'total_qty' => (int) $item->total_qty,
                    'total_revenue' => (float) $item->total_revenue,
                ];
            })
            ->toArray();
    }

    private function formatPeriod($period)
    {
        switch ($this->period) {
            case 'daily':
                return Carbon::parse($period)->format('M d');
            case 'weekly':
                // Convert YEARWEEK to readable format
                $year = substr($period, 0, 4);
                $week = substr($period, 4, 2);
                return "Week {$week}, {$year}";
            case 'monthly':
                return Carbon::createFromFormat('Y-m', $period)->format('M Y');
            default:
                return $period;
        }
    }

    public function render()
    {
        return view('livewire.analytics-dashboard', [
            'metrics' => $this->metrics,
            'revenueData' => $this->revenueData,
            'ordersData' => $this->ordersData,
            'bestSellingProducts' => $this->bestSellingProducts,
        ]);
    }
}
