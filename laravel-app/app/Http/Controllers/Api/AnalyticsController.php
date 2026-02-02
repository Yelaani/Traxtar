<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends BaseApiController
{
    /**
     * Get analytics data for the specified period.
     * 
     * Sanctum-protected endpoint. Admin roles are also enforced at API level 
     * using Sanctum token abilities, ensuring consistent authorization across layers.
     */
    public function index(Request $request): JsonResponse
    {
        // Ensure user is admin
        if (!$request->user()->isAdmin()) {
            return $this->errorResponse('Unauthorized. Admin access required.', 403);
        }

        $period = $request->input('period', 'daily'); // 'daily', 'weekly', 'monthly'
        
        $dateRange = $this->getDateRange($period);
        
        $metrics = [
            'total_orders' => $this->getTotalOrders($dateRange),
            'total_revenue' => $this->getTotalRevenue($dateRange),
            'new_users' => $this->getNewUsers($dateRange),
        ];

        $revenueData = $this->getRevenueByPeriod($dateRange, $period);
        $ordersData = $this->getOrdersByPeriod($dateRange, $period);
        $bestSellingProducts = $this->getBestSellingProducts($dateRange);

        return $this->successResponse([
            'period' => $period,
            'metrics' => $metrics,
            'revenue_data' => $revenueData,
            'orders_data' => $ordersData,
            'best_selling_products' => $bestSellingProducts,
        ]);
    }

    private function getDateRange($period)
    {
        $endDate = Carbon::now();
        
        switch ($period) {
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

    private function getRevenueByPeriod($dateRange, $period)
    {
        $query = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        switch ($period) {
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
        
        return $data->map(function ($item) use ($period) {
            return [
                'period' => $this->formatPeriod($item->period, $period),
                'revenue' => (float) $item->revenue,
            ];
        })->values()->toArray();
    }

    private function getOrdersByPeriod($dateRange, $period)
    {
        $query = Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        
        switch ($period) {
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
        
        return $data->map(function ($item) use ($period) {
            return [
                'period' => $this->formatPeriod($item->period, $period),
                'count' => (int) $item->count,
            ];
        })->values()->toArray();
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
            ->values()
            ->toArray();
    }

    private function formatPeriod($period, $periodType)
    {
        switch ($periodType) {
            case 'daily':
                return Carbon::parse($period)->format('M d');
            case 'weekly':
                $year = substr($period, 0, 4);
                $week = substr($period, 4, 2);
                return "Week {$week}, {$year}";
            case 'monthly':
                return Carbon::createFromFormat('Y-m', $period)->format('M Y');
            default:
                return $period;
        }
    }
}
