<div>
    <!-- Period Selector -->
    <div class="card p-4 mb-6">
        <div class="flex items-center gap-4">
            <label class="text-sm font-medium text-neutral-700">Time Period:</label>
            <div class="flex gap-2">
                <button 
                    wire:click="$set('period', 'daily')"
                    class="px-4 py-2 rounded-md text-sm font-medium transition {{ $period === 'daily' ? 'bg-blue-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200' }}">
                    Daily
                </button>
                <button 
                    wire:click="$set('period', 'weekly')"
                    class="px-4 py-2 rounded-md text-sm font-medium transition {{ $period === 'weekly' ? 'bg-blue-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200' }}">
                    Weekly
                </button>
                <button 
                    wire:click="$set('period', 'monthly')"
                    class="px-4 py-2 rounded-md text-sm font-medium transition {{ $period === 'monthly' ? 'bg-blue-600 text-white' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200' }}">
                    Monthly
                </button>
            </div>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid gap-6 md:grid-cols-3 mb-6">
        <div class="card p-6">
            <h3 class="font-semibold mb-2 text-neutral-600">Total Orders</h3>
            <p class="text-3xl font-bold">{{ number_format($metrics['total_orders']) }}</p>
        </div>
        <div class="card p-6">
            <h3 class="font-semibold mb-2 text-neutral-600">Total Revenue</h3>
            <p class="text-3xl font-bold">LKR {{ number_format($metrics['total_revenue'], 2) }}</p>
        </div>
        <div class="card p-6">
            <h3 class="font-semibold mb-2 text-neutral-600">New Users</h3>
            <p class="text-3xl font-bold">{{ number_format($metrics['new_users']) }}</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid gap-6 md:grid-cols-2 mb-6">
        <!-- Revenue Line Chart -->
        <div class="card p-6">
            <h3 class="font-semibold mb-4">Revenue Over Time</h3>
            <div wire:ignore>
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>

        <!-- Orders Bar Chart -->
        <div class="card p-6">
            <h3 class="font-semibold mb-4">Orders Per Period</h3>
            <div wire:ignore>
                <canvas id="ordersChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Best Selling Products -->
    <div class="card p-6">
        <h3 class="font-semibold mb-4">Best-Selling Products</h3>
        @if(count($bestSellingProducts) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-neutral-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase">Quantity Sold</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-700 uppercase">Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-neutral-200">
                        @foreach($bestSellingProducts as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-neutral-900">{{ $product['product_name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-600">{{ number_format($product['total_qty']) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-neutral-600">LKR {{ number_format($product['total_revenue'], 2) }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-neutral-600">No sales data available for this period.</p>
        @endif
    </div>

    <!-- Chart.js Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <script>
        let revenueChartInstance = null;
        let ordersChartInstance = null;

        function initCharts() {
            const revenueData = @json($revenueData);
            const ordersData = @json($ordersData);

            // Revenue Line Chart
            const revenueCtx = document.getElementById('revenueChart');
            if (revenueCtx) {
                if (revenueChartInstance) {
                    revenueChartInstance.destroy();
                }
                
                revenueChartInstance = new Chart(revenueCtx, {
                    type: 'line',
                    data: {
                        labels: revenueData.map(item => item.period),
                        datasets: [{
                            label: 'Revenue (LKR)',
                            data: revenueData.map(item => item.revenue),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'LKR ' + value.toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Orders Bar Chart
            const ordersCtx = document.getElementById('ordersChart');
            if (ordersCtx) {
                if (ordersChartInstance) {
                    ordersChartInstance.destroy();
                }
                
                ordersChartInstance = new Chart(ordersCtx, {
                    type: 'bar',
                    data: {
                        labels: ordersData.map(item => item.period),
                        datasets: [{
                            label: 'Orders',
                            data: ordersData.map(item => item.count),
                            backgroundColor: 'rgba(34, 197, 94, 0.6)',
                            borderColor: 'rgb(34, 197, 94)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        }

        // Initialize charts on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }

        // Update charts when Livewire updates (period changes)
        document.addEventListener('livewire:init', () => {
            Livewire.on('period-updated', () => {
                setTimeout(initCharts, 100);
            });
        });

        // Listen for Livewire component updates
        document.addEventListener('livewire:update', () => {
            setTimeout(initCharts, 100);
        });
    </script>
</div>
