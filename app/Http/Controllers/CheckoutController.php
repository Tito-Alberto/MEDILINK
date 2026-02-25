<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\WalletAccount;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private const SYSTEM_COMMISSION_RATE = 0.10;
    private const DELIVERY_FEE_SMALL = 500.00;
    private const DELIVERY_FEE_MEDIUM = 500.00;
    private const DELIVERY_FEE_LARGE = 500.00;
    private const DELIVERY_FEE_XL = 500.00;

    public function show()
    {
        [$items, $subtotal] = $this->buildCartItems();

        if (empty($items)) {
            return redirect()->route('cart.index')->with('status', 'Seu carrinho estÃ¡ vazio.');
        }

        $walletPurchaseBalance = null;
        if (auth()->check()) {
            $wallet = $this->ensureWalletAccount('user', auth()->id(), 'Carteira de ' . auth()->user()->name);
            $walletPurchaseBalance = (float) $wallet->balance;
        }

        $deliveryFee = $this->calculateDeliveryFee($subtotal);
        $total = round($subtotal + $deliveryFee, 2);

        return view('storefront.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'deliveryFee' => $deliveryFee,
            'total' => $total,
            'walletPurchaseBalance' => $walletPurchaseBalance,
            'canUseWalletPayment' => auth()->check() && $walletPurchaseBalance !== null,
        ]);
    }

    public function place(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'customer_address' => ['required', 'string', 'max:255'],
            'customer_nif' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['nullable', 'string', 'in:cash,wallet'],
        ]);

        [$items, $subtotal] = $this->buildCartItems();
        if (empty($items)) {
            return redirect()->route('cart.index')->with('status', 'Seu carrinho estÃ¡ vazio.');
        }
        $deliveryFee = $this->calculateDeliveryFee($subtotal);
        $total = round($subtotal + $deliveryFee, 2);

        $paymentMethod = (string) ($data['payment_method'] ?? 'cash');
        if ($paymentMethod === 'wallet' && ! auth()->check()) {
            return back()->withErrors([
                'payment_method' => 'Inicie sessÃ£o para pagar com a sua carteira.',
            ])->withInput();
        }


        if (auth()->check()) {
            $wallet = $this->ensureWalletAccount('user', auth()->id(), 'Carteira de ' . auth()->user()->name);
            if ((float) $wallet->balance >= (float) $total) {
                $paymentMethod = 'wallet';
            }
        }

        try {
            $order = DB::transaction(function () use ($data, $items, $subtotal, $deliveryFee, $total, $paymentMethod) {
            $this->reserveOrderItemsStock($items);

            $order = Order::create([
                'customer_user_id' => auth()->id(),
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'],
                'customer_nif' => $data['customer_nif'] ?? null,
                'notes' => $data['notes'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'tax_amount' => 0,
                'total' => $total,
                'status' => 'novo',
            ]);

            if ($paymentMethod === 'wallet') {
                $this->debitCustomerWalletForOrder($order, $total);
            }

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'unit_price' => $item['product']->price,
                    'quantity' => $item['qty'],
                    'line_total' => $item['line_total'],
                ]);
            }

            $this->creditWalletsForOrder($order, $items, $deliveryFee);

            return $order;
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'payment_method' => $e->getMessage(),
            ])->withInput();
        }

        session()->forget('cart');

        return redirect()->route('orders.show', $order);
    }

    public function showOrder(Order $order)
    {
        if (
            auth()->check()
            && (int) $order->customer_user_id === (int) auth()->id()
            && $order->customer_confirmed_notified_at
            && ! $order->customer_confirmed_seen_at
        ) {
            $order->customer_confirmed_seen_at = now();
            $order->save();
        }

        $order->load('items');

        return view('storefront.success', [
            'order' => $order,
        ]);
    }

    private function buildCartItems(): array
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return [[], 0.0];
        }

        $products = Product::query()
            ->with('pharmacy:id,name')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = [];
        $subtotal = 0.0;

        foreach ($cart as $productId => $qty) {
            if (! $products->has($productId)) {
                continue;
            }

            $product = $products->get($productId);
            $lineTotal = (float) $product->price * $qty;
            $subtotal += $lineTotal;

            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'line_total' => $lineTotal,
            ];
        }

        return [$items, round($subtotal, 2)];
    }

    private function reserveOrderItemsStock(array $items): void
    {
        foreach ($items as $item) {
            $productId = (int) ($item['product']->id ?? 0);
            $requestedQty = (int) ($item['qty'] ?? 0);

            if ($productId <= 0 || $requestedQty <= 0) {
                throw new \RuntimeException('Item inválido no carrinho para concluir o pedido.');
            }

            /** @var \App\Models\Product|null $lockedProduct */
            $lockedProduct = Product::query()->lockForUpdate()->find($productId);

            if (! $lockedProduct || ! (bool) $lockedProduct->is_active) {
                throw new \RuntimeException('Um dos produtos do carrinho já não está disponível.');
            }

            $currentStock = (int) $lockedProduct->stock;
            if ($currentStock < $requestedQty) {
                throw new \RuntimeException(
                    'Stock insuficiente para "' . $lockedProduct->name . '". Disponível: ' . $currentStock . ' unidade(s).'
                );
            }

            $lockedProduct->stock = $currentStock - $requestedQty;
            $lockedProduct->save();
        }
    }

    private function calculateDeliveryFee(float $subtotal): float
    {
        $subtotal = round(max(0, $subtotal), 2);

        if ($subtotal <= 0) {
            return 0.0;
        }

        return match (true) {
            $subtotal < 5000 => self::DELIVERY_FEE_SMALL,
            $subtotal < 15000 => self::DELIVERY_FEE_MEDIUM,
            $subtotal < 30000 => self::DELIVERY_FEE_LARGE,
            default => self::DELIVERY_FEE_XL,
        };
    }

    private function creditWalletsForOrder(Order $order, array $items, float $deliveryFee = 0.0): void
    {
        if (empty($items) && round($deliveryFee, 2) <= 0) {
            return;
        }

        $systemWallet = $this->ensureWalletAccount('system', null, 'Carteira do sistema');
        $groupedByPharmacy = [];

        foreach ($items as $item) {
            $product = $item['product'];
            $pharmacyId = $product->pharmacy_id ? (int) $product->pharmacy_id : null;
            $groupKey = $pharmacyId ? 'pharmacy:' . $pharmacyId : 'system:direct';

            if (! isset($groupedByPharmacy[$groupKey])) {
                $groupedByPharmacy[$groupKey] = [
                    'pharmacy_id' => $pharmacyId,
                    'pharmacy_name' => $product->pharmacy?->name,
                    'gross_total' => 0.0,
                ];
            }

            $groupedByPharmacy[$groupKey]['gross_total'] += (float) $item['line_total'];
        }

        foreach ($groupedByPharmacy as $group) {
            $grossTotal = round((float) $group['gross_total'], 2);
            if ($grossTotal <= 0) {
                continue;
            }

            $pharmacyId = $group['pharmacy_id'];

            if ($pharmacyId) {
                $systemShare = round($grossTotal * self::SYSTEM_COMMISSION_RATE, 2);
                $pharmacyShare = round($grossTotal - $systemShare, 2);
                $pharmacyLabel = 'Carteira da farmÃ¡cia ' . ($group['pharmacy_name'] ?: ('#' . $pharmacyId));

                if ($pharmacyShare > 0) {
                    $pharmacyWallet = $this->ensureWalletAccount('pharmacy', $pharmacyId, $pharmacyLabel);
                    $this->postWalletCredit(
                        $pharmacyWallet,
                        $pharmacyShare,
                        'pharmacy_sale',
                        'Quota da farmÃ¡cia no pedido #' . $order->id,
                        'ORD-' . $order->id . '-PHA-' . $pharmacyId,
                        [
                            'order_id' => $order->id,
                            'pharmacy_id' => $pharmacyId,
                            'gross_total' => $grossTotal,
                            'commission_rate' => self::SYSTEM_COMMISSION_RATE,
                        ]
                    );
                }

                if ($systemShare > 0) {
                    $this->postWalletCredit(
                        $systemWallet,
                        $systemShare,
                        'system_fee',
                        'ComissÃ£o do sistema no pedido #' . $order->id,
                        'ORD-' . $order->id . '-SYS-' . $pharmacyId,
                        [
                            'order_id' => $order->id,
                            'pharmacy_id' => $pharmacyId,
                            'gross_total' => $grossTotal,
                            'commission_rate' => self::SYSTEM_COMMISSION_RATE,
                        ]
                    );
                }

                continue;
            }

            $this->postWalletCredit(
                $systemWallet,
                $grossTotal,
                'system_sale',
                'Venda sem farmÃ¡cia associada no pedido #' . $order->id,
                'ORD-' . $order->id . '-SYS-DIRECT',
                [
                    'order_id' => $order->id,
                    'gross_total' => $grossTotal,
                ]
            );
        }

        $deliveryFee = round($deliveryFee, 2);
        if ($deliveryFee > 0) {
            $this->postWalletCredit(
                $systemWallet,
                $deliveryFee,
                'delivery_fee',
                'Taxa de entrega do pedido #' . $order->id,
                'ORD-' . $order->id . '-DELIVERY',
                [
                    'order_id' => $order->id,
                    'delivery_fee' => $deliveryFee,
                ]
            );
        }
    }

    private function debitCustomerWalletForOrder(Order $order, float $total): void
    {
        if (! auth()->check()) {
            throw new \RuntimeException('Inicie sessÃ£o para pagar com carteira.');
        }

        $user = auth()->user();
        $wallet = $this->ensureWalletAccount('user', $user->id, 'Carteira de ' . $user->name);
        $amount = round($total, 2);

        if ($amount <= 0) {
            return;
        }

        $lockedWallet = WalletAccount::query()->lockForUpdate()->findOrFail($wallet->id);
        $currentBalance = round((float) $lockedWallet->balance, 2);

        if ($currentBalance < $amount) {
            throw new \RuntimeException('Saldo insuficiente na sua carteira para concluir a compra.');
        }

        $nextBalance = round($currentBalance - $amount, 2);
        $lockedWallet->balance = $nextBalance;
        $lockedWallet->save();

        WalletTransaction::create([
            'wallet_account_id' => $lockedWallet->id,
            'direction' => 'debit',
            'category' => 'customer_purchase',
            'amount' => $amount,
            'balance_after' => $nextBalance,
            'status' => 'posted',
            'reference_code' => 'ORD-' . $order->id . '-CUS-' . $user->id,
            'description' => 'Pagamento com carteira do pedido #' . $order->id,
            'meta' => [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'payment_method' => 'wallet',
            ],
            'related_type' => 'order',
            'related_id' => $order->id,
            'posted_at' => now(),
        ]);
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
        ?string $referenceCode = null,
        array $meta = []
    ): void {
        $amount = round($amount, 2);
        if ($amount <= 0) {
            return;
        }

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
            'meta' => $meta ?: null,
            'related_type' => 'order',
            'related_id' => $meta['order_id'] ?? null,
            'posted_at' => now(),
        ]);
    }
}
