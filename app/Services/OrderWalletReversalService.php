<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WalletAccount;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class OrderWalletReversalService
{
    public function reverseOrderCredits(Order $order): void
    {
        $orderTransactions = WalletTransaction::query()
            ->where('related_type', 'order')
            ->where('related_id', $order->id)
            ->where('status', 'posted')
            ->whereIn('category', ['pharmacy_sale', 'system_fee', 'system_sale', 'customer_purchase'])
            ->orderBy('id')
            ->get();

        if ($orderTransactions->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($order, $orderTransactions) {
            foreach ($orderTransactions as $sourceTx) {
                $alreadyReversed = WalletTransaction::query()
                    ->where('related_type', 'wallet_transaction')
                    ->where('related_id', $sourceTx->id)
                    ->where('status', 'posted')
                    ->exists();

                if ($alreadyReversed) {
                    continue;
                }

                $wallet = WalletAccount::query()->lockForUpdate()->findOrFail($sourceTx->wallet_account_id);
                $amount = round((float) $sourceTx->amount, 2);

                if ($amount <= 0) {
                    continue;
                }

                $currentBalance = round((float) $wallet->balance, 2);
                $reverseDirection = ((string) $sourceTx->direction === 'debit') ? 'credit' : 'debit';
                $nextBalance = $reverseDirection === 'debit'
                    ? round($currentBalance - $amount, 2)
                    : round($currentBalance + $amount, 2);

                if ($nextBalance < 0) {
                    throw new \RuntimeException(
                        'Não foi possível estornar o pedido #' . $order->id . ' por saldo insuficiente na carteira ' . ($wallet->label ?: ('#' . $wallet->id)) . '.'
                    );
                }

                $wallet->balance = $nextBalance;
                $wallet->save();

                WalletTransaction::create([
                    'wallet_account_id' => $wallet->id,
                    'direction' => $reverseDirection,
                    'category' => $this->reversalCategory((string) $sourceTx->category),
                    'amount' => $amount,
                    'balance_after' => $nextBalance,
                    'status' => 'posted',
                    'reference_code' => $this->reversalReferenceCode($sourceTx),
                    'description' => $this->reversalDescription($order, $sourceTx),
                    'meta' => [
                        'order_id' => $order->id,
                        'reversal_for_transaction_id' => $sourceTx->id,
                        'reversal_for_category' => $sourceTx->category,
                    ],
                    'related_type' => 'wallet_transaction',
                    'related_id' => $sourceTx->id,
                    'posted_at' => now(),
                ]);
            }
        });
    }

    private function reversalCategory(string $sourceCategory): string
    {
        return match ($sourceCategory) {
            'pharmacy_sale' => 'reversal_pharmacy_sale',
            'system_fee' => 'reversal_system_fee',
            'system_sale' => 'reversal_system_sale',
            'customer_purchase' => 'reversal_customer_purchase',
            default => 'reversal',
        };
    }

    private function reversalReferenceCode(WalletTransaction $sourceTx): string
    {
        $base = trim((string) $sourceTx->reference_code);

        return $base !== '' ? $base . '-REV' : 'REV-TX-' . $sourceTx->id;
    }

    private function reversalDescription(Order $order, WalletTransaction $sourceTx): string
    {
        $source = match ((string) $sourceTx->category) {
            'pharmacy_sale' => 'quota da farmácia',
            'system_fee' => 'comissão do sistema',
            'system_sale' => 'venda do sistema',
            'customer_purchase' => 'pagamento do cliente com carteira',
            default => 'lançamento do pedido',
        };

        return 'Estorno automático (' . $source . ') do pedido #' . $order->id;
    }
}
