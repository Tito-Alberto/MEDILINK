<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PharmacyController extends Controller
{
    public function status(Request $request)
    {
        $pharmacy = $request->user()->pharmacy;

        return view('pharmacy.status', [
            'pharmacy' => $pharmacy,
        ]);
    }

    public function create(Request $request)
    {
        if ($request->user()->pharmacy) {
            return redirect()->route('pharmacy.status');
        }

        return view('pharmacy.create');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->pharmacy) {
            return redirect()->route('pharmacy.status');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160', Rule::unique('pharmacies', 'name')],
            'responsible_name' => ['required', 'string', 'max:160'],
            'nif' => ['required', 'string', 'max:40'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['required', 'string', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        Pharmacy::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'responsible_name' => $data['responsible_name'],
            'nif' => $data['nif'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'address' => $data['address'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('pharmacy.status')
            ->with('status', 'Pedido enviado. Aguarde aprovação do admin.');
    }
}
