<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\WalletAccount;
use App\Models\WalletTransaction;
use App\Services\OrderWalletReversalService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SyncOrderWalletHistory extends Command
{
    private const SYSTEM_COMMISSION_RATE = 0.10;

    private const CANCELED_STATUSES = [
        'cancelado',
        'cancelada',
        'cancelled',
        'canceled',
        'rejeitado',
        'rejeitada',
        'rejected',
        'recusado',
        'recusada',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:sync-order-history {--dry-run : Simula sem gravar alterações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza carteiras (farmácias/sistema/entrega) com base no histórico de pedidos';

    public function handle(OrderWalletReversalService $reversalService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $orders = Order::query()
            ->select(['id', 'status', 'delivery_fee', 'created_at'])
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            $this->info('Nenhum pedido encontrado.');

            return self::SUCCESS;
        }

        $summary = [
            'orders_total' => $orders->count(),
            'orders_settled_checked' => 0,
            'orders_cancelled_checked' => 0,
            'orders_with_new_transactions' => 0,
            'transactions_created' => 0,
            'transactions_skipped_existing' => 0,
            'transactions_skipped_no_amount' => 0,
            'cancelled_reversals_attempted' => 0,
            'orders_without_items' => 0,
        ];

        $this->line($dryRun
            ? 'Modo simulação: nenhuma alteração será gravada.'
            : 'A sincronizar históricos de pedidos para carteiras...');

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $order) {
            $status = $this->normalizeStatus((string) $order->status);

            if ($this->isCanceledStatus($status)) {
                $summary['orders_cancelled_checked']++;
                $summary['cancelled_reversals_attempted']++;

                if (! $dryRun) {
                    $reversalService->reverseOrderCredits($order);
                }

                $bar->advance();
                continue;
            }

            $summary['orders_settled_checked']++;

            $items = $this->loadOrderItemsForWalletSettlement((int) $order->id);
            if ($items->isEmpty()) {
                $summary['orders_without_items']++;
                $bar->advance();
                continue;
            }

            $result = $this->syncOrderSettlementCredits($order, $items, $dryRun);
            $summary['transactions_created'] += $result['created'];
            $summary['transactions_skipped_existing'] += $result['skipped_existing'];
            $summary['transactions_skipped_no_amount'] += $result['skipped_no_amount'];
            if ($result['created'] > 0) {
                $summary['orders_with_new_transactions']++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Pedidos analisados', $summary['orders_total']],
                ['Pedidos ativos processados', $summary['orders_settled_checked']],
                ['Pedidos cancelados/rejeitados verificados', $summary['orders_cancelled_checked']],
                ['Pedidos sem itens', $summary['orders_without_items']],
                ['Pedidos com novos lançamentos', $summary['orders_with_new_transactions']],
                ['Transações criadas', $summary['transactions_created']],
                ['Transações já existentes (ignoradas)', $summary['transactions_skipped_existing']],
                ['Transações sem montante (>0) (ignoradas)', $summary['transactions_skipped_no_amount']],
                ['Tentativas de estorno em cancelados', $summary['cancelled_reversals_attempted']],
            ]
        );

        $this->line('Nota: esta sincronização atualiza créditos de farmácia/sistema/entrega. Débitos antigos de cliente (wallet) só existem se já foram registados na altura da venda.');

        return self::SUCCESS;
    }

    private function loadOrderItemsForWalletSettlement(int $orderId): Collection
    {
        return DB::table('order_items')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('pharmacies', 'pharmacies.id', '=', 'products.pharmacy_id')
            ->where('order_items.order_id', $orderId)
            ->select(
                'order_items.product_id',
                'order_items.product_name',
                'order_items.line_total',
                'products.pharmacy_id',
                'pharmacies.name as pharmacy_name'
            )
            ->get();
    }

    private function syncOrderSettlementCredits(Order $order, Collection $items, bool $dryRun): array
    {
        $groupedByPharmacy = [];

        foreach ($items as $item) {
            $pharmacyId = $item->pharmacy_id ? (int) $item->pharmacy_id : null;
            $groupKey = $pharmacyId ? 'pharmacy:' . $pharmacyId : 'system:direct';

            if (! isset($groupedByPharmacy[$groupKey])) {
                $groupedByPharmacy[$groupKey] = [
                    'pharmacy_id' => $pharmacyId,
                    'pharmacy_name' => $item->pharmacy_name,
                    'gross_total' => 0.0,
                ];
            }

            $groupedByPharmacy[$groupKey]['gross_total'] += (float) ($item->line_total ?? 0);
        }

        $created = 0;
        $skippedExisting = 0;
        $skippedNoAmount = 0;

        $applyCredit = function (
            WalletAccount $wallet,
            float $amount,
            string $category,
            string $description,
            string $referenceCode,
            array $meta
        ) use (&$created, &$skippedExisting, &$skippedNoAmount, $order, $dryRun): void {
            $amount = round($amount, 2);

            if ($amount <= 0) {
                $skippedNoAmount++;
                return;
            }

            if ($this->orderWalletTransactionExists((int) $order->id, $category, $referenceCode)) {
                $skippedExisting++;
                return;
            }

            if ($dryRun) {
                $created++;
                return;
            }

            $this->postWalletCredit(
                $wallet,
                $amount,
                $category,
                $description,
                $referenceCode,
                $meta + [
                    'order_id' => $order->id,
                    'backfilled_by' => 'wallet:sync-order-history',
                ],
                $order->id
            );
            $created++;
        };

        $systemWallet = null;
        $getSystemWallet = function () use (&$systemWallet): WalletAccount {
            if (! $systemWallet) {
                $systemWallet = $this->ensureWalletAccount('system', null, 'Carteira do sistema');
            }

            return $systemWallet;
        };

        foreach ($groupedByPharmacy as $group) {
            $grossTotal = round((float) $group['gross_total'], 2);

            if ($grossTotal <= 0) {
                $skippedNoAmount++;
                continue;
            }

            $pharmacyId = $group['pharmacy_id'];

            if ($pharmacyId) {
                $systemShare = round($grossTotal * self::SYSTEM_COMMISSION_RATE, 2);
                $pharmacyShare = round($grossTotal - $systemShare, 2);
                $pharmacyLabel = 'Carteira da farmácia ' . ($group['pharmacy_name'] ?: ('#' . $pharmacyId));

                if ($pharmacyShare > 0) {
                    $pharmacyWallet = $this->ensureWalletAccount('pharmacy', (int) $pharmacyId, $pharmacyLabel);
                    $applyCredit(
                        $pharmacyWallet,
                        $pharmacyShare,
                        'pharmacy_sale',
                        'Quota da farmácia no pedido #' . $order->id . ' (sincronização histórica)',
                        'ORD-' . $order->id . '-PHA-' . $pharmacyId,
                        [
                            'pharmacy_id' => (int) $pharmacyId,
                            'gross_total' => $grossTotal,
                            'commission_rate' => self::SYSTEM_COMMISSION_RATE,
                        ]
                    );
                } else {
                    $skippedNoAmount++;
                }

                if ($systemShare > 0) {
                    $applyCredit(
                        $getSystemWallet(),
                        $systemShare,
                        'system_fee',
                        'Comissão do sistema no pedido #' . $order->id . ' (sincronização histórica)',
                        'ORD-' . $order->id . '-SYS-' . $pharmacyId,
                        [
                            'pharmacy_id' => (int) $pharmacyId,
                            'gross_total' => $grossTotal,
                            'commission_rate' => self::SYSTEM_COMMISSION_RATE,
                        ]
                    );
                } else {
                    $skippedNoAmount++;
                }

                continue;
            }

            $applyCredit(
                $getSystemWallet(),
                $grossTotal,
                'system_sale',
                'Venda sem farmácia associada no pedido #' . $order->id . ' (sincronização histórica)',
                'ORD-' . $order->id . '-SYS-DIRECT',
                [
                    'gross_total' => $grossTotal,
                ]
            );
        }

        $deliveryFee = round((float) ($order->delivery_fee ?? 0), 2);
        if ($deliveryFee > 0) {
            $applyCredit(
                $getSystemWallet(),
                $deliveryFee,
                'delivery_fee',
                'Taxa de entrega do pedido #' . $order->id . ' (sincronização histórica)',
                'ORD-' . $order->id . '-DELIVERY',
                [
                    'delivery_fee' => $deliveryFee,
                ]
            );
        } else {
            $skippedNoAmount++;
        }

        return [
            'created' => $created,
            'skipped_existing' => $skippedExisting,
            'skipped_no_amount' => $skippedNoAmount,
        ];
    }

    private function orderWalletTransactionExists(int $orderId, string $category, string $referenceCode): bool
    {
        return WalletTransaction::query()
            ->where('related_type', 'order')
            ->where('related_id', $orderId)
            ->where('status', 'posted')
            ->where('category', $category)
            ->where('reference_code', $referenceCode)
            ->exists();
    }

    private function ensureWalletAccount(string $ownerType, ?int $ownerId, string $label): WalletAccount
    {
        return WalletAccount::query()->firstOrCreate(
            [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
            ],
            [
                'label' => $label,
                'currency' => 'AOA',
                'balance' => 0,
                'is_active' => true,
            ]
        );
    }

    private function postWalletCredit(
        WalletAccount $wallet,
        float $amount,
        string $category,
        string $description,
        string $referenceCode,
        array $meta,
        int $orderId
    ): void {
        DB::transaction(function () use ($wallet, $amount, $category, $description, $referenceCode, $meta, $orderId) {
            $lockedWallet = WalletAccount::query()->lockForUpdate()->findOrFail($wallet->id);
            $nextBalance = round(((float) $lockedWallet->balance) + $amount, 2);

            $lockedWallet->balance = $nextBalance;
            $lockedWallet->save();

            WalletTransaction::create([
                'wallet_account_id' => $lockedWallet->id,
                'direction' => 'credit',
                'category' => $category,
                'amount' => $amount,
                'balance_after' => $nextBalance,
                'status' => 'posted',
                'reference_code' => $referenceCode,
                'description' => $description,
                'meta' => $meta,
                'related_type' => 'order',
                'related_id' => $orderId,
                'posted_at' => now(),
            ]);
        });
    }

    private function normalizeStatus(string $status): string
    {
        return mb_strtolower(trim($status));
    }

    private function isCanceledStatus(string $status): bool
    {
        return in_array($status, self::CANCELED_STATUSES, true);
    }
}

