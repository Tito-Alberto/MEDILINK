<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Itens do Pedido - Medlink</title>
        <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <a href="#" class="text-blue-600 hover:text-blue-900 mb-4 inline-block">&larr; Voltar</a>
            <h1 class="text-3xl font-bold text-gray-900">Itens do Pedido #{{ $order->id ?? '-' }}</h1>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Quantidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Preço Unitário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Subtotal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($orderItems ?? [] as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $item->product->name ?? 'Produto' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item->quantity ?? 0 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                Kz {{ number_format($item->price ?? 0, 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                Kz {{ number_format(($item->quantity ?? 0) * ($item->price ?? 0), 2, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="#" class="text-yellow-600 hover:text-yellow-900 font-medium">Editar</a>
                                <button class="text-red-600 hover:text-red-900 font-medium">Remover</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Nenhum item neste pedido
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(!empty($orderItems) && count($orderItems) > 0)
            <div class="mt-6 bg-white rounded-lg shadow p-6 flex justify-end">
                <div class="text-right">
                    <p class="text-gray-600 mb-2">Total do Pedido:</p>
                    <p class="text-3xl font-bold text-blue-600">
                        Kz {{ number_format($order->total ?? 0, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        @endif
    </div>
</body>
</html>











