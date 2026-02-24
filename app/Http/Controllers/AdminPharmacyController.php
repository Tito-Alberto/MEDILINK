<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use Illuminate\Http\Request;

class AdminPharmacyController extends Controller
{
    public function index()
    {
        return view('admin.pharmacies', [
            'pending' => Pharmacy::where('status', 'pending')->latest()->get(),
            'pharmacies' => Pharmacy::latest()->get(),
        ]);
    }

    public function approve(Request $request, Pharmacy $pharmacy)
    {
        $pharmacy->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Farmacia aprovada com sucesso.');
    }

    public function reject(Request $request, Pharmacy $pharmacy)
    {
        $pharmacy->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Farmacia recusada.');
    }

    public function destroy(Pharmacy $pharmacy)
    {
        $productCount = $pharmacy->products()->count();
        $pharmacyName = $pharmacy->name;

        // Preserve order history by keeping products, but detach and hide them.
        $pharmacy->products()->update([
            'pharmacy_id' => null,
            'is_active' => false,
        ]);

        $pharmacy->delete();

        return back()->with(
            'status',
            "Farmacia removida: {$pharmacyName}. {$productCount} produto(s) foram desativado(s)."
        );
    }
}