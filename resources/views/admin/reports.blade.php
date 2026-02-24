@extends('layouts.storefront')

@section('title', html_entity_decode('Relat&oacute;rios do Sistema - Medlink', ENT_QUOTES, 'UTF-8'))

@section('content')
    @php
        $selectedReportCategory = (string) ($filters['report_category'] ?? '');
        $selectedReportCategoryLabel = $selectedReportCategory !== '' ? ($reportCategoryOptions[$selectedReportCategory] ?? null) : null;
    @endphp

    <style>
        @media print {
            @page {
                margin: 10mm;
            }

            header,
            footer,
            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
            }

            .glass {
                box-shadow: none !important;
                border-color: #d1d5db !important;
                background: #fff !important;
            }

            .print-grid-break {
                break-inside: avoid;
            }

            .print-header {
                display: block !important;
                margin-bottom: 12px;
            }

            .print-header h1 {
                margin: 0;
                font-size: 20px;
                line-height: 1.2;
            }

            .print-header p {
                margin: 4px 0 0;
                color: #475569;
                font-size: 12px;
            }

            table {
                font-size: 11px !important;
            }
        }

        .print-header {
            display: none;
        }
    </style>

    <script>
        (() => {
            const originalTitle = document.title;
            const printTitle = 'Relat\u00f3rios do Sistema - Medlink';

            const applyPrintTitle = () => {
                document.title = printTitle;
            };

            const restoreTitle = () => {
                document.title = originalTitle;
            };

            window.printReport = () => {
                applyPrintTitle();
                window.print();
            };

            const applyReportCategoryFilter = () => {
                const categoryField = document.querySelector('select[name="report_category"]');
                const selectedCategory = categoryField ? categoryField.value : @json($filters['report_category'] ?? '');
                const cards = document.querySelectorAll('[data-report-card]');
                const grids = document.querySelectorAll('[data-report-grid]');
                const summarySection = document.querySelector('[data-report-summary]');

                cards.forEach((card) => {
                    const key = card.getAttribute('data-report-card') || '';
                    const isVisible = selectedCategory === '' || selectedCategory === key;
                    card.classList.toggle('hidden', !isVisible);
                });

                grids.forEach((grid) => {
                    const hasVisibleCards = Array.from(grid.querySelectorAll('[data-report-card]')).some((card) => !card.classList.contains('hidden'));
                    grid.classList.toggle('hidden', !hasVisibleCards);
                });

                if (summarySection) {
                    summarySection.classList.toggle('hidden', selectedCategory !== '');
                }
            };

            window.addEventListener('DOMContentLoaded', () => {
                applyReportCategoryFilter();
                document.querySelector('select[name="report_category"]')?.addEventListener('change', applyReportCategoryFilter);
            });
            window.addEventListener('beforeprint', applyPrintTitle);
            window.addEventListener('afterprint', restoreTitle);
        })();
    </script>

    <div class="print-header">
        <h1>Relat&oacute;rios do sistema</h1>
        <p>
            Emitido em {{ now()->format('d/m/Y H:i') }}
            @if (!empty($filters['date_from']) || !empty($filters['date_to']))
                | Per&iacute;odo:
                {{ $filters['date_from'] ?: 'in&iacute;cio' }}
                at&eacute;
                {{ $filters['date_to'] ?: 'hoje' }}
            @endif
            @if (!empty($filters['q']))
                | Pesquisa: {{ $filters['q'] }}
            @endif
            @if (!empty($selectedReportCategoryLabel))
                | Categoria: {{ $selectedReportCategoryLabel }}
            @endif
        </p>
    </div>

    <section class="glass rounded-3xl p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Relat&oacute;rio</p>
                <h1 class="brand-title mt-2 text-4xl text-slate-900">Relat&oacute;rios do sistema</h1>
                <p class="mt-3 max-w-3xl text-sm text-slate-600">
                    O administrador pode imprimir todos os dados ou aplicar filtros por per&iacute;odo e categoria.
                </p>
            </div>
            <div class="no-print flex flex-wrap gap-2">
                <a class="rounded-full border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:border-lime-300" href="{{ route('admin.reports.index') }}">
                    Ver tudo
                </a>
                <button class="rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800" type="button" onclick="window.printReport()">
                    Imprimir relat&oacute;rio
                </button>
            </div>
        </div>

        <form class="no-print mt-6 grid gap-4 md:grid-cols-[1fr_1fr_1.8fr_auto]" method="GET" action="{{ route('admin.reports.index') }}">
            <label class="grid gap-1 text-sm text-slate-600">
                <span>Data inicial</span>
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900" type="date" name="date_from" value="{{ $filters['date_from'] }}" />
            </label>
            <label class="grid gap-1 text-sm text-slate-600">
                <span>Data final</span>
                <input class="rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900" type="date" name="date_to" value="{{ $filters['date_to'] }}" />
            </label>
            <label class="grid gap-1 text-sm text-slate-600">
                <span>Selecione uma categoria</span>
                <select class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900" name="report_category">
                    @foreach ($reportCategoryOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['report_category'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button class="rounded-xl bg-lime-400 px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-lime-300" type="submit">Filtrar</button>
            </div>
        </form>

        @if ($hasFilters)
            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Filtros ativos:
                <span class="font-semibold">
                    {{ $filters['date_from'] ?: 'sem data inicial' }}
                    at&eacute;
                    {{ $filters['date_to'] ?: 'sem data final' }}
                </span>
                @if (!empty($filters['q']))
                    | Pesquisa: <span class="font-semibold">{{ $filters['q'] }}</span>
                @endif
                @if (!empty($selectedReportCategoryLabel))
                    | Categoria: <span class="font-semibold">{{ $selectedReportCategoryLabel }}</span>
                @endif
            </div>
        @endif
    </section>

    <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4" data-report-summary>
        <div class="glass print-grid-break rounded-3xl p-5">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Utilizadores</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['users_count'] }}</p>
            <p class="mt-1 text-sm text-slate-500">Admins: {{ $summary['admin_users_count'] }}</p>
        </div>
        <div class="glass print-grid-break rounded-3xl p-5">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Farm&aacute;cias</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['pharmacies_count'] }}</p>
            <p class="mt-1 text-sm text-slate-500">
                Aprovadas: {{ $summary['pharmacies_approved_count'] }} | Pendentes: {{ $summary['pharmacies_pending_count'] }}
            </p>
        </div>
        <div class="glass print-grid-break rounded-3xl p-5">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Produtos</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['products_count'] }}</p>
            <p class="mt-1 text-sm text-slate-500">Ativos: {{ $summary['active_products_count'] }}</p>
        </div>
        <div class="glass print-grid-break rounded-3xl p-5">
            <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Pedidos / Vendas</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $summary['orders_count'] }}</p>
            <p class="mt-1 text-sm text-slate-500">
                Total: Kz {{ number_format((float) $summary['sales_total'], 2, ',', '.') }}
            </p>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2" data-report-grid>
        <div class="glass print-grid-break rounded-3xl p-6" data-report-card="sales_by_pharmacy">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Vendas</p>
                    <h2 class="brand-title text-3xl text-slate-900">Resumo de vendas por farm&aacute;cia</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">{{ $salesByPharmacy->count() }} linhas</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Farm&aacute;cia</th>
                            <th class="px-4 py-3 text-right">Pedidos</th>
                            <th class="px-4 py-3 text-right">Unidades</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($salesByPharmacy as $row)
                            <tr>
                                <td class="px-4 py-3">{{ $row->pharmacy_name }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->orders_count }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->units_sold }}</td>
                                <td class="px-4 py-3 text-right font-semibold">Kz {{ number_format((float) $row->sales_total, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem dados de vendas para os filtros aplicados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass print-grid-break rounded-3xl p-6" data-report-card="orders_summary">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Totais financeiros</p>
                    <h2 class="brand-title text-3xl text-slate-900">Resumo de pedidos</h2>
                </div>
            </div>

            <div class="mt-4 grid gap-3">
                <div class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Subtotal</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">Kz {{ number_format((float) $orderTotals['subtotal_sum'], 2, ',', '.') }}</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Taxa de entrega</p>
                        <p class="mt-2 text-xl font-semibold text-slate-900">Kz {{ number_format((float) $orderTotals['delivery_sum'], 2, ',', '.') }}</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white/70 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Impostos</p>
                        <p class="mt-2 text-xl font-semibold text-slate-900">Kz {{ number_format((float) $orderTotals['tax_sum'], 2, ',', '.') }}</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-lime-200 bg-lime-50 p-4">
                    <p class="text-xs uppercase tracking-[0.2em] text-lime-700">Total final de vendas</p>
                    <p class="mt-2 text-2xl font-semibold text-slate-900">Kz {{ number_format((float) $orderTotals['total_sum'], 2, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2" data-report-grid>
        <div class="glass print-grid-break rounded-3xl p-6" data-report-card="customers_with_orders">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Clientes</p>
                    <h2 class="brand-title text-3xl text-slate-900">Clientes que fizeram pedidos</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">{{ $customersWithOrders->count() }} registos</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Cliente</th>
                            <th class="px-4 py-3 text-left">Telefone</th>
                            <th class="px-4 py-3 text-right">Pedidos</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($customersWithOrders as $row)
                            <tr>
                                <td class="px-4 py-3">{{ $row->customer_name }}</td>
                                <td class="px-4 py-3">{{ $row->customer_phone }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->orders_count }}</td>
                                <td class="px-4 py-3 text-right">Kz {{ number_format((float) $row->total_spent, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem clientes com pedidos neste filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass print-grid-break rounded-3xl p-6" data-report-card="users_registrations">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Utilizadores</p>
                    <h2 class="brand-title text-3xl text-slate-900">Cadastros de utilizadores</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">{{ $users->count() }} registos</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Nome</th>
                            <th class="px-4 py-3 text-left">Email</th>
                            <th class="px-4 py-3 text-left">Perfil</th>
                            <th class="px-4 py-3 text-left">Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-4 py-3">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">{{ $user->is_admin ? 'Admin' : 'Utilizador' }}</td>
                                <td class="px-4 py-3">{{ $user->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem utilizadores para os filtros aplicados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2" data-report-grid>
        <div class="glass print-grid-break rounded-3xl p-6" data-report-card="approved_pharmacies">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Farm&aacute;cias</p>
                    <h2 class="brand-title text-3xl text-slate-900">Farm&aacute;cias aprovadas</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">{{ $approvedPharmacies->count() }} registos</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Farm&aacute;cia</th>
                            <th class="px-4 py-3 text-left">Respons&aacute;vel</th>
                            <th class="px-4 py-3 text-left">Contacto</th>
                            <th class="px-4 py-3 text-left">Aprovada em</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($approvedPharmacies as $pharmacy)
                            <tr>
                                <td class="px-4 py-3">{{ $pharmacy->name }}</td>
                                <td class="px-4 py-3">{{ $pharmacy->responsible_name }}</td>
                                <td class="px-4 py-3">{{ $pharmacy->phone }}</td>
                                <td class="px-4 py-3">{{ $pharmacy->approved_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem farm&aacute;cias aprovadas para este filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass print-grid-break rounded-3xl p-6" data-report-card="pharmacies_with_products">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Produtos</p>
                    <h2 class="brand-title text-3xl text-slate-900">Farm&aacute;cias que registaram produtos</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">{{ $productRegistrationsByPharmacy->count() }} linhas</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Farm&aacute;cia</th>
                            <th class="px-4 py-3 text-right">Registos</th>
                            <th class="px-4 py-3 text-right">Ativos</th>
                            <th class="px-4 py-3 text-left">&Uacute;ltimo registo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($productRegistrationsByPharmacy as $row)
                            <tr>
                                <td class="px-4 py-3">{{ $row->pharmacy_name }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->products_registered }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->active_products }}</td>
                                <td class="px-4 py-3">{{ $row->last_product_at ? \Illuminate\Support\Carbon::parse($row->last_product_at)->format('d/m/Y H:i') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="4">Sem registos de produtos para este filtro.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2" data-report-grid>
        <div class="glass print-grid-break rounded-3xl p-6" data-report-card="stock_by_pharmacy">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Stock</p>
                    <h2 class="brand-title text-3xl text-slate-900">Stock por farm&aacute;cia</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">Estado atual</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Farm&aacute;cia</th>
                            <th class="px-4 py-3 text-right">Produtos</th>
                            <th class="px-4 py-3 text-right">Ativos</th>
                            <th class="px-4 py-3 text-right">Stock</th>
                            <th class="px-4 py-3 text-right">Sem stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($stockByPharmacy as $row)
                            <tr>
                                <td class="px-4 py-3">{{ $row->name }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->products_total }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->active_products }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->stock_total }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) $row->out_of_stock_count }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="5">Sem dados de stock para mostrar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass print-grid-break rounded-3xl p-6" data-report-card="registered_pharmacies">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Cadastros</p>
                    <h2 class="brand-title text-3xl text-slate-900">Farm&aacute;cias registadas</h2>
                </div>
                <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">{{ $pharmacies->count() }} registos</span>
            </div>

            <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white/90 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Farm&aacute;cia</th>
                            <th class="px-4 py-3 text-left">Estado</th>
                            <th class="px-4 py-3 text-right">Prod. ativos</th>
                            <th class="px-4 py-3 text-right">Stock ativo</th>
                            <th class="px-4 py-3 text-left">Registo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                        @forelse ($pharmacies as $pharmacy)
                            <tr>
                                <td class="px-4 py-3">{{ $pharmacy->name }}</td>
                                <td class="px-4 py-3">
                                    {{
                                        match (strtolower((string) $pharmacy->status)) {
                                            'approved' => 'APROVADO',
                                            'pending' => 'PENDENTE',
                                            'rejected' => 'REJEITADO',
                                            default => strtoupper((string) $pharmacy->status),
                                        }
                                    }}
                                </td>
                                <td class="px-4 py-3 text-right">{{ (int) ($pharmacy->active_products_count ?? 0) }}</td>
                                <td class="px-4 py-3 text-right">{{ (int) ($pharmacy->active_stock_sum ?? 0) }}</td>
                                <td class="px-4 py-3">{{ $pharmacy->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-4 py-4 text-slate-500" colspan="5">Sem farm&aacute;cias para os filtros aplicados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="mt-8 glass print-grid-break rounded-3xl p-6" data-report-card="orders_list">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-lime-700">Pedidos</p>
                <h2 class="brand-title text-3xl text-slate-900">Lista de vendas / pedidos</h2>
            </div>
            <span class="rounded-full border border-slate-300 px-3 py-1 text-xs text-slate-600">{{ $orders->count() }} registos</span>
        </div>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-white/90 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Pedido</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-left">Telefone</th>
                        <th class="px-4 py-3 text-left">Estado</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-left">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/40 text-slate-700">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-4 py-3">#{{ $order->id }}</td>
                            <td class="px-4 py-3">{{ $order->customer_name }}</td>
                            <td class="px-4 py-3">{{ $order->customer_phone }}</td>
                            <td class="px-4 py-3">{{ strtoupper((string) $order->status) }}</td>
                            <td class="px-4 py-3 text-right font-semibold">Kz {{ number_format((float) $order->total, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $order->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td class="px-4 py-4 text-slate-500" colspan="6">Sem pedidos para os filtros aplicados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
