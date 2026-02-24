<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WalletAccount;
use App\Models\WalletTopUpRequest;
use App\Models\WalletTransaction;
use App\Models\WalletWithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    private const DEFAULT_COMMISSION_RATE = 0.10;

    public function userIndex(Request $request)
    {
        $user = $request->user()->loadMissing('pharmacy');

        $userWallet = $this->ensureWalletAccount('user', $user->id, 'Carteira de ' . $user->name);
        $pharmacy = $user->pharmacy;
        $pharmacyWallet = null;

        if ($pharmacy) {
            $pharmacyWallet = $this->ensureWalletAccount('pharmacy', $pharmacy->id, 'Carteira da farmácia ' . $pharmacy->name);
        }

        $walletIds = collect([$userWallet->id, $pharmacyWallet?->id])->filter()->values();

        $transactions = WalletTransaction::query()
            ->with(['walletAccount', 'performer'])
            ->whereIn('wallet_account_id', $walletIds)
            ->latest()
            ->limit(50)
            ->get();

        $topUpRequests = WalletTopUpRequest::query()
            ->with(['pharmacy', 'handler', 'walletAccount'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(30)
            ->get();

        $withdrawRequests = WalletWithdrawRequest::query()
            ->with(['pharmacy', 'handler', 'walletAccount'])
            ->where('user_id', $user->id)
            ->latest()
            ->limit(30)
            ->get();

        $pendingTopUp = $topUpRequests->firstWhere('status', 'pending');
        $purchasingBalance = (float) $userWallet->balance;
        $commissionRate = self::DEFAULT_COMMISSION_RATE;
        $pharmacySalesSummary = null;

        if ($pharmacy) {
            $grossSales = (float) (DB::table('order_items')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('products.pharmacy_id', $pharmacy->id)
                ->sum('order_items.line_total') ?? 0);

            $ordersCount = (int) (DB::table('order_items')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->selectRaw('COUNT(DISTINCT order_items.order_id) as orders_count')
                ->where('products.pharmacy_id', $pharmacy->id)
                ->value('orders_count') ?? 0);

            $systemShare = round($grossSales * $commissionRate, 2);
            $pharmacyShare = round($grossSales - $systemShare, 2);

            $pharmacySalesSummary = [
                'gross_sales' => $grossSales,
                'orders_count' => $ordersCount,
                'commission_rate' => $commissionRate,
                'system_share' => $systemShare,
                'pharmacy_share' => $pharmacyShare,
            ];
        }

        return view('wallet.index', [
            'userWallet' => $userWallet,
            'pharmacy' => $pharmacy,
            'pharmacyWallet' => $pharmacyWallet,
            'topUpRequests' => $topUpRequests,
            'withdrawRequests' => $withdrawRequests,
            'transactions' => $transactions,
            'pendingTopUp' => $pendingTopUp,
            'purchasingBalance' => $purchasingBalance,
            'pharmacySalesSummary' => $pharmacySalesSummary,
            'commissionRatePercent' => (int) round($commissionRate * 100),
        ]);
    }

    public function storeTopUpRequest(Request $request)
    {
        $user = $request->user()->loadMissing('pharmacy');

        $data = $request->validate([
            'target' => ['required', 'in:user,pharmacy'],
            'amount' => ['required', 'numeric', 'min:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        [$wallet, $pharmacy] = $this->resolveUserWalletTarget($user, $data['target']);

        $topUpRequest = $this->createTopUpRequest(
            wallet: $wallet,
            userId: $user->id,
            pharmacyId: $pharmacy?->id,
            amount: (float) $data['amount'],
            notes: $data['notes'] ?? null
        );

        return back()
            ->with('status', 'Referência de pagamento gerada com sucesso. Após o pagamento por referência, o saldo será creditado automaticamente.')
            ->with('wallet_topup_reference', $topUpRequest->payment_reference)
            ->with('wallet_topup_code', $topUpRequest->reference_code);
    }

    public function confirmTopUpByReference(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'payment_reference' => ['required', 'string', 'max:120'],
            'amount' => ['nullable', 'numeric', 'min:100'],
        ]);

        $paymentReference = trim((string) $data['payment_reference']);

        $pendingRequest = WalletTopUpRequest::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('payment_reference', $paymentReference)
            ->latest()
            ->first();

        if (! $pendingRequest) {
            return back()->withErrors([
                'payment_reference' => 'Nenhum carregamento pendente encontrado para esta referência.',
            ])->withInput();
        }

        if (isset($data['amount']) && round((float) $data['amount'], 2) !== round((float) $pendingRequest->amount, 2)) {
            return back()->withErrors([
                'amount' => 'O valor informado não coincide com o valor da referência pendente.',
            ])->withInput();
        }

        try {
            $this->approveTopUpRequest($pendingRequest, null, 'Carregamento confirmado por referência (integração automática)');
        } catch (\RuntimeException $e) {
            return back()->withErrors([
                'payment_reference' => $e->getMessage(),
            ]);
        }

        return back()->with('status', 'Pagamento confirmado por referência. O valor foi creditado automaticamente na sua carteira.');
    }

    public function paymentReferenceCallback(Request $request)
    {
        $expectedToken = (string) config('app.wallet_callback_token', env('WALLET_CALLBACK_TOKEN', ''));
        $providedToken = (string) $request->header('X-Wallet-Callback-Token', $request->input('token', ''));

        if ($expectedToken !== '' && ! hash_equals($expectedToken, $providedToken)) {
            return response()->json([
                'ok' => false,
                'message' => 'Token de callback inválido.',
            ], 403);
        }

        $data = $request->validate([
            'payment_reference' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:100'],
            'payment_status' => ['required', 'string', 'in:paid,success,completed'],
            'provider_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $topUpRequest = WalletTopUpRequest::query()
            ->where('payment_reference', trim((string) $data['payment_reference']))
            ->latest()
            ->first();

        if (! $topUpRequest) {
            return response()->json([
                'ok' => false,
                'message' => 'Referência não encontrada.',
            ], 404);
        }

        if ($topUpRequest->status !== 'pending') {
            return response()->json([
                'ok' => true,
                'message' => 'Carregamento já tratado.',
                'request_status' => $topUpRequest->status,
                'request_id' => $topUpRequest->id,
            ]);
        }

        if (round((float) $data['amount'], 2) !== round((float) $topUpRequest->amount, 2)) {
            return response()->json([
                'ok' => false,
                'message' => 'Valor do pagamento não corresponde ao pedido pendente.',
            ], 422);
        }

        try {
            $description = 'Carregamento confirmado por callback de pagamento';
            if (! empty($data['provider_reference'])) {
                $description .= ' (' . $data['provider_reference'] . ')';
            }
            $this->approveTopUpRequest($topUpRequest, null, $description);
        } catch (\RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Carregamento creditado automaticamente.',
            'request_id' => $topUpRequest->id,
            'wallet_account_id' => $topUpRequest->wallet_account_id,
        ]);
    }

    public function storeWithdrawRequest(Request $request)
    {
        $user = $request->user()->loadMissing('pharmacy');

        $data = $request->validate([
            'target' => ['required', 'in:user,pharmacy'],
            'amount' => ['required', 'numeric', 'min:100'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_holder' => ['required', 'string', 'max:160'],
            'iban' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        [$wallet, $pharmacy] = $this->resolveUserWalletTarget($user, $data['target']);
        $wallet->refresh();

        if ((float) $wallet->balance < (float) $data['amount']) {
            return back()->withErrors([
                'amount' => 'Saldo insuficiente na carteira selecionada para solicitar a transferência.',
            ])->withInput();
        }

        WalletWithdrawRequest::create([
            'wallet_account_id' => $wallet->id,
            'user_id' => $user->id,
            'pharmacy_id' => $pharmacy?->id,
            'amount' => (float) $data['amount'],
            'bank_name' => $data['bank_name'] ?? null,
            'account_holder' => $data['account_holder'],
            'iban' => strtoupper(trim($data['iban'])),
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Pedido de transferência registado. O administrador vai validar e processar o pagamento.');
    }

    public function adminIndex(Request $request)
    {
        $systemWallet = $this->ensureWalletAccount('system', null, 'Carteira do sistema');
        $commissionRate = self::DEFAULT_COMMISSION_RATE;

        $filters = $request->validate([
            'status' => ['nullable', 'string', 'max:20'],
        ]);

        $statusFilter = trim((string) ($filters['status'] ?? ''));

        $wallets = WalletAccount::query()
            ->orderByDesc('balance')
            ->limit(120)
            ->get();

        $topUpRequests = WalletTopUpRequest::query()
            ->with(['user', 'pharmacy', 'handler', 'walletAccount'])
            ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
            ->latest()
            ->limit(80)
            ->get();

        $withdrawRequests = WalletWithdrawRequest::query()
            ->with(['user', 'pharmacy', 'handler', 'walletAccount'])
            ->when($statusFilter !== '', fn ($query) => $query->where('status', $statusFilter))
            ->latest()
            ->limit(80)
            ->get();

        $recentTransactions = WalletTransaction::query()
            ->with(['walletAccount', 'performer'])
            ->latest()
            ->limit(100)
            ->get();

        $walletSummary = [
            'wallets_count' => WalletAccount::count(),
            'users_wallets_count' => WalletAccount::where('owner_type', 'user')->count(),
            'pharmacies_wallets_count' => WalletAccount::where('owner_type', 'pharmacy')->count(),
            'wallets_balance_sum' => (float) (WalletAccount::sum('balance') ?? 0),
            'pending_topups_count' => WalletTopUpRequest::where('status', 'pending')->count(),
            'pending_withdrawals_count' => WalletWithdrawRequest::whereIn('status', ['pending', 'processing'])->count(),
        ];

        $salesAllocationRows = DB::table('order_items')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('pharmacies', 'pharmacies.id', '=', 'products.pharmacy_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->selectRaw("COALESCE(pharmacies.name, 'Sem farmácia') as pharmacy_name")
            ->selectRaw('COUNT(DISTINCT orders.id) as orders_count')
            ->selectRaw('SUM(order_items.line_total) as gross_sales')
            ->groupBy('pharmacies.id', 'pharmacies.name')
            ->orderByDesc('gross_sales')
            ->limit(50)
            ->get()
            ->map(function ($row) use ($commissionRate) {
                $gross = (float) ($row->gross_sales ?? 0);
                $systemShare = round($gross * $commissionRate, 2);

                $row->system_share = $systemShare;
                $row->pharmacy_share = round($gross - $systemShare, 2);
                $row->commission_rate = $commissionRate;

                return $row;
            });

        $salesTotals = [
            'gross_sales' => (float) ($salesAllocationRows->sum('gross_sales') ?? 0),
            'system_share' => (float) ($salesAllocationRows->sum('system_share') ?? 0),
            'pharmacy_share' => (float) ($salesAllocationRows->sum('pharmacy_share') ?? 0),
            'orders_count' => (int) ($salesAllocationRows->sum('orders_count') ?? 0),
            'commission_rate_percent' => (int) round($commissionRate * 100),
        ];

        return view('admin.wallet', [
            'systemWallet' => $systemWallet,
            'wallets' => $wallets,
            'walletSummary' => $walletSummary,
            'topUpRequests' => $topUpRequests,
            'withdrawRequests' => $withdrawRequests,
            'recentTransactions' => $recentTransactions,
            'salesAllocationRows' => $salesAllocationRows,
            'salesTotals' => $salesTotals,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function adminCreateTopUpReference(Request $request)
    {
        $data = $request->validate([
            'user_lookup' => ['required', 'string', 'max:255'],
            'target' => ['required', 'in:user,pharmacy'],
            'amount' => ['required', 'numeric', 'min:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $lookup = trim((string) $data['user_lookup']);
        $user = User::query()
            ->with('pharmacy')
            ->when(is_numeric($lookup), function ($query) use ($lookup) {
                $query->where('id', (int) $lookup);
            }, function ($query) use ($lookup) {
                $query->where('email', $lookup);
            })
            ->first();

        if (! $user) {
            return back()->withErrors([
                'user_lookup' => 'Utilizador não encontrado (use ID ou email).',
            ])->withInput();
        }

        [$wallet, $pharmacy] = $this->resolveUserWalletTarget($user, (string) $data['target']);

        $topUpRequest = $this->createTopUpRequest(
            wallet: $wallet,
            userId: $user->id,
            pharmacyId: $pharmacy?->id,
            amount: (float) $data['amount'],
            notes: $data['notes'] ?? null
        );

        return back()->with(
            'status',
            'Referência gerada para ' . $user->name . ': ' . $topUpRequest->payment_reference . ' (valor Kz ' . number_format((float) $topUpRequest->amount, 2, ',', '.') . ')'
        );
    }

    public function adminApproveTopUp(Request $request, WalletTopUpRequest $topUpRequest)
    {
        if ($topUpRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Este carregamento já foi tratado.']);
        }

        try {
            $this->approveTopUpRequest($topUpRequest, $request->user()->id, 'Carregamento aprovado pelo administrador');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', 'Carregamento aprovado e saldo atualizado na carteira.');
    }

    public function adminRejectTopUp(Request $request, WalletTopUpRequest $topUpRequest)
    {
        if ($topUpRequest->status !== 'pending') {
            return back()->withErrors(['status' => 'Este carregamento já foi tratado.']);
        }

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $topUpRequest->update([
            'status' => 'rejected',
            'notes' => $data['notes'] ?? $topUpRequest->notes,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('status', 'Pedido de carregamento rejeitado.');
    }

    public function adminUpdateWithdrawStatus(Request $request, WalletWithdrawRequest $withdrawRequest)
    {
        $data = $request->validate([
            'action' => ['required', 'in:processing,paid,rejected'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $action = $data['action'];

        if (! in_array($withdrawRequest->status, ['pending', 'processing'], true)) {
            return back()->withErrors(['status' => 'Este pedido de transferência já foi concluído.']);
        }

        if ($action === 'paid') {
            try {
                DB::transaction(function () use ($request, $withdrawRequest, $data) {
                    $wallet = WalletAccount::query()->lockForUpdate()->findOrFail($withdrawRequest->wallet_account_id);

                    if ((float) $wallet->balance < (float) $withdrawRequest->amount) {
                        throw new \RuntimeException('Saldo insuficiente para concluir o pagamento.');
                    }

                    $this->postTransaction(
                        wallet: $wallet,
                        direction: 'debit',
                        amount: (float) $withdrawRequest->amount,
                        category: 'withdrawal',
                        description: 'Levantamento/transferência pago pelo administrador',
                        referenceCode: 'WD-' . $withdrawRequest->id,
                        performedBy: $request->user()->id,
                        relatedType: 'wallet_withdraw_request',
                        relatedId: $withdrawRequest->id
                    );

                    $withdrawRequest->update([
                        'status' => 'paid',
                        'notes' => $data['notes'] ?? $withdrawRequest->notes,
                        'handled_by' => $request->user()->id,
                        'handled_at' => now(),
                    ]);
                });
            } catch (\RuntimeException $e) {
                return back()->withErrors(['status' => $e->getMessage()]);
            }

            return back()->with('status', 'Transferência marcada como paga e saldo debitado.');
        }

        $withdrawRequest->update([
            'status' => $action,
            'notes' => $data['notes'] ?? $withdrawRequest->notes,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('status', $action === 'processing'
            ? 'Transferência marcada como em processamento.'
            : 'Pedido de transferência rejeitado.');
    }

    public function adminSystemAdjustment(Request $request)
    {
        $data = $request->validate([
            'direction' => ['required', 'in:credit,debit'],
            'amount' => ['required', 'numeric', 'min:1'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $systemWallet = $this->ensureWalletAccount('system', null, 'Carteira do sistema');

        try {
            DB::transaction(function () use ($request, $data, $systemWallet) {
                $wallet = WalletAccount::query()->lockForUpdate()->findOrFail($systemWallet->id);

                if ($data['direction'] === 'debit' && (float) $wallet->balance < (float) $data['amount']) {
                    throw new \RuntimeException('Saldo insuficiente na carteira do sistema.');
                }

                $this->postTransaction(
                    wallet: $wallet,
                    direction: $data['direction'],
                    amount: (float) $data['amount'],
                    category: 'adjustment',
                    description: $data['description'],
                    referenceCode: $this->generateUniqueReference('MLK-AJT'),
                    performedBy: $request->user()->id
                );
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return back()->with('status', 'Lançamento manual registado na carteira do sistema.');
    }

    private function resolveUserWalletTarget($user, string $target): array
    {
        if ($target === 'pharmacy') {
            if (! $user->pharmacy) {
                abort(422, 'O utilizador não tem farmácia associada.');
            }

            $pharmacy = $user->pharmacy;
            $wallet = $this->ensureWalletAccount('pharmacy', $pharmacy->id, 'Carteira da farmácia ' . $pharmacy->name);

            return [$wallet, $pharmacy];
        }

        $wallet = $this->ensureWalletAccount('user', $user->id, 'Carteira de ' . $user->name);

        return [$wallet, null];
    }

    private function createTopUpRequest(
        WalletAccount $wallet,
        int $userId,
        ?int $pharmacyId,
        float $amount,
        ?string $notes = null
    ): WalletTopUpRequest {
        return WalletTopUpRequest::create([
            'wallet_account_id' => $wallet->id,
            'user_id' => $userId,
            'pharmacy_id' => $pharmacyId,
            'amount' => round($amount, 2),
            'status' => 'pending',
            'reference_code' => $this->generateUniqueReference('MLK-REC'),
            'payment_reference' => $this->generateUniquePaymentReference(),
            'notes' => $notes,
        ]);
    }

    private function approveTopUpRequest(WalletTopUpRequest $topUpRequest, ?int $performedBy, string $description): void
    {
        if ($topUpRequest->status !== 'pending') {
            throw new \RuntimeException('Este carregamento já foi tratado.');
        }

        DB::transaction(function () use ($topUpRequest, $performedBy, $description) {
            $lockedRequest = WalletTopUpRequest::query()->lockForUpdate()->findOrFail($topUpRequest->id);
            if ($lockedRequest->status !== 'pending') {
                throw new \RuntimeException('Este carregamento já foi tratado.');
            }

            $wallet = WalletAccount::query()->lockForUpdate()->findOrFail($lockedRequest->wallet_account_id);

            $this->postTransaction(
                wallet: $wallet,
                direction: 'credit',
                amount: (float) $lockedRequest->amount,
                category: 'topup',
                description: $description,
                referenceCode: $lockedRequest->reference_code,
                performedBy: $performedBy,
                relatedType: 'wallet_top_up_request',
                relatedId: $lockedRequest->id
            );

            $lockedRequest->update([
                'status' => 'approved',
                'handled_by' => $performedBy,
                'handled_at' => now(),
            ]);
        });

        $topUpRequest->refresh();
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

    private function postTransaction(
        WalletAccount $wallet,
        string $direction,
        float $amount,
        string $category,
        ?string $description = null,
        ?string $referenceCode = null,
        ?int $performedBy = null,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): WalletTransaction {
        $amount = round($amount, 2);
        $currentBalance = (float) $wallet->balance;
        $nextBalance = $direction === 'debit'
            ? round($currentBalance - $amount, 2)
            : round($currentBalance + $amount, 2);

        if ($nextBalance < 0) {
            throw new \RuntimeException('O saldo não pode ficar negativo.');
        }

        $wallet->balance = $nextBalance;
        $wallet->save();

        return WalletTransaction::create([
            'wallet_account_id' => $wallet->id,
            'direction' => $direction,
            'category' => $category,
            'amount' => $amount,
            'balance_after' => $nextBalance,
            'status' => 'posted',
            'reference_code' => $referenceCode,
            'description' => $description,
            'performed_by' => $performedBy,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'posted_at' => now(),
        ]);
    }

    private function generateUniqueReference(string $prefix): string
    {
        do {
            $candidate = $prefix . '-' . now()->format('ymd') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (
            WalletTopUpRequest::query()->where('reference_code', $candidate)->exists() ||
            WalletTransaction::query()->where('reference_code', $candidate)->exists()
        );

        return $candidate;
    }

    private function generateUniquePaymentReference(): string
    {
        do {
            $candidate = 'REF-' . now()->format('ymd') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (WalletTopUpRequest::query()->where('payment_reference', $candidate)->exists());

        return $candidate;
    }
}
