<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-brand-900">Dashboard</h2>
                <p class="text-sm text-slate-500 mt-0.5">
                    Welcome back, {{ Auth::user()->name }} —
                    <span class="font-medium text-accent-600">{{ Auth::user()->getRoleNames()->first() ?? 'No role' }}</span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('sales.create')
                <button class="inline-flex items-center gap-2 rounded-lg bg-brand-800 px-3 sm:px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700 focus:ring-2 focus:ring-accent-500 focus:ring-offset-2 whitespace-nowrap">
                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    New Invoice
                </button>
                @endcan
                @can('sourcing.create')
                <button class="inline-flex items-center gap-2 rounded-lg bg-white px-3 sm:px-4 py-2 text-sm font-semibold text-brand-800 ring-1 ring-slate-200 hover:bg-slate-50 whitespace-nowrap">
                    Purchase Entry
                </button>
                @endcan
            </div>
        </div>
    </x-slot>

    {{-- Demo figures until the ERP modules land (README Phase 1) --}}
    @php
        $stats = [
            ['label' => "Today's Sales", 'value' => '৳ 1,24,500', 'delta' => '+12.4%', 'up' => true, 'icon' => 'sales'],
            ['label' => "Today's Collection", 'value' => '৳ 86,200', 'delta' => '+5.1%', 'up' => true, 'icon' => 'collection'],
            ['label' => 'Total Receivable (Due)', 'value' => '৳ 4,32,900', 'delta' => '-2.3%', 'up' => false, 'icon' => 'due'],
            ['label' => 'Low Stock Items', 'value' => '14', 'delta' => '3 critical', 'up' => false, 'icon' => 'stock'],
        ];
    @endphp

    <div class="space-y-6">
        <!-- Stat cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach ($stats as $stat)
            <div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="absolute right-0 top-0 h-20 w-20 translate-x-6 -translate-y-6 rounded-full bg-gradient-to-br from-brand-100 to-accent-300/40"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-brand-900">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs font-semibold {{ $stat['up'] ? 'text-emerald-600' : 'text-rose-500' }}">
                            {{ $stat['delta'] }}
                            <span class="font-normal text-slate-400">vs yesterday</span>
                        </p>
                    </div>
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-brand-700 to-brand-900 text-accent-400">
                        @if ($stat['icon'] === 'sales')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/></svg>
                        @elseif ($stat['icon'] === 'collection')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        @elseif ($stat['icon'] === 'due')
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                        @else
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                        @endif
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Charts row -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="xl:col-span-2 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-brand-900">Sales vs Collection</h3>
                        <p class="text-xs text-slate-400">Last 7 days (demo data)</p>
                    </div>
                    <span class="rounded-full bg-accent-500/10 px-3 py-1 text-xs font-semibold text-accent-600">Weekly</span>
                </div>
                <div class="h-72"><canvas id="salesChart"></canvas></div>
            </div>

            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="mb-4">
                    <h3 class="font-bold text-brand-900">Receivable Aging</h3>
                    <p class="text-xs text-slate-400">Outstanding dues by age (demo data)</p>
                </div>
                <div class="h-56"><canvas id="agingChart"></canvas></div>
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-brand-800"></span>0–30 days</span><span class="font-semibold text-brand-900">৳ 2,10,000</span></div>
                    <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-brand-500"></span>31–60 days</span><span class="font-semibold text-brand-900">৳ 1,40,400</span></div>
                    <div class="flex items-center justify-between"><span class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-accent-500"></span>60+ days</span><span class="font-semibold text-brand-900">৳ 82,500</span></div>
                </div>
            </div>
        </div>

        <!-- Tables row -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <!-- Recent invoices -->
            <div class="xl:col-span-2 rounded-2xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-5 pt-5 pb-3">
                    <h3 class="font-bold text-brand-900">Recent Invoices</h3>
                    <a href="#" class="text-xs font-semibold text-accent-600 hover:text-accent-500">View all &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[480px] text-sm">
                        <thead>
                            <tr class="border-y border-slate-100 bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-3 font-semibold">Invoice</th>
                                <th class="px-5 py-3 font-semibold">Customer</th>
                                <th class="px-5 py-3 font-semibold">Amount</th>
                                <th class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php
                                $statusClasses = [
                                    'Paid' => 'bg-emerald-50 text-emerald-600 ring-emerald-200',
                                    'Partial' => 'bg-amber-50 text-amber-600 ring-amber-200',
                                    'Due' => 'bg-rose-50 text-rose-600 ring-rose-200',
                                ];
                            @endphp
                            @foreach ([
                                ['INV-00241', 'Rahim Traders', '৳ 45,000', 'Paid'],
                                ['INV-00240', 'Karim & Sons', '৳ 28,500', 'Partial'],
                                ['INV-00239', 'Metro Distribution', '৳ 96,200', 'Due'],
                                ['INV-00238', 'City Mart', '৳ 12,750', 'Paid'],
                                ['INV-00237', 'Bhuiyan Enterprise', '৳ 61,900', 'Partial'],
                            ] as [$no, $customer, $amount, $status])
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-semibold text-brand-800 whitespace-nowrap">{{ $no }}</td>
                                <td class="px-5 py-3 text-slate-600 whitespace-nowrap">{{ $customer }}</td>
                                <td class="px-5 py-3 font-medium text-slate-800 whitespace-nowrap">{{ $amount }}</td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $statusClasses[$status] }}">{{ $status }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low stock alerts -->
            <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-brand-900">Low Stock Alerts</h3>
                    <span class="rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-600 ring-1 ring-rose-200">14 items</span>
                </div>
                <ul class="space-y-3">
                    @foreach ([
                        ['Cement (50kg bag)', '12 bags left', 'reorder at 50'],
                        ['MS Rod 12mm', '0.4 ton left', 'reorder at 2 ton'],
                        ['PVC Pipe 4"', '35 pcs left', 'reorder at 100'],
                        ['Paint - White 5L', '8 cans left', 'reorder at 20'],
                    ] as [$item, $left, $reorder])
                    <li class="flex items-start justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">{{ $item }}</p>
                            <p class="text-xs text-slate-400">{{ $reorder }}</p>
                        </div>
                        <span class="shrink-0 text-xs font-bold text-rose-500">{{ $left }}</span>
                    </li>
                    @endforeach
                </ul>
                @can('inventory.view')
                <a href="#" class="mt-4 block rounded-lg bg-brand-800/5 px-4 py-2 text-center text-xs font-semibold text-brand-800 hover:bg-brand-800/10">
                    Open Inventory &rarr;
                </a>
                @endcan
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const brand = { deep: '#0b2a6b', mid: '#1857c4', cyan: '#06b6d4', cyanSoft: 'rgba(6, 182, 212, .15)', blueSoft: 'rgba(11, 42, 107, .08)' };
            const days = ['Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon'];

            new Chart(document.getElementById('salesChart'), {
                type: 'bar',
                data: {
                    labels: days,
                    datasets: [
                        {
                            label: 'Sales',
                            data: [86000, 112000, 94000, 138000, 152000, 71000, 124500],
                            backgroundColor: brand.deep,
                            borderRadius: 6,
                            barPercentage: .55,
                        },
                        {
                            label: 'Collection',
                            data: [64000, 90000, 81000, 101000, 128000, 66000, 86200],
                            backgroundColor: brand.cyan,
                            borderRadius: 6,
                            barPercentage: .55,
                        },
                    ],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
                    scales: {
                        y: { grid: { color: 'rgba(100,116,139,.1)' }, ticks: { callback: v => '৳' + (v / 1000) + 'k' } },
                        x: { grid: { display: false } },
                    },
                },
            });

            new Chart(document.getElementById('agingChart'), {
                type: 'doughnut',
                data: {
                    labels: ['0–30 days', '31–60 days', '60+ days'],
                    datasets: [{
                        data: [210000, 140400, 82500],
                        backgroundColor: [brand.deep, brand.mid, brand.cyan],
                        borderWidth: 0,
                        hoverOffset: 6,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: { legend: { display: false } },
                },
            });
        });
    </script>
</x-app-layout>
