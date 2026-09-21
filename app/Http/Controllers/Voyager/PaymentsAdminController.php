<?php

namespace App\Http\Controllers\Voyager;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

/**
 * Read-only admin screen for gateway payments. Follows the hand-rolled admin
 * pattern already used by Voyager\PushController / ImportLoyaltyPointController
 * (plain controller + Blade view under resources/views/cms, guarded by the
 * admin.user middleware in routes/web.php).
 */
class PaymentsAdminController extends Controller
{
    public function __construct()
    {
        // Skip the base admin-section bootstrapping (same choice as VitasController);
        // this screen does not use $this->sections.
    }

    public function index(Request $request)
    {
        $statuses = array_merge(Payment::OPEN_STATUSES, Payment::TERMINAL_STATUSES);

        $payments = Payment::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('channel'), fn ($q) => $q->where('channel', $request->input('channel')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->input('q'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('reference', 'like', "%{$term}%")
                        ->orWhere('loan_number', 'like', "%{$term}%")
                        ->orWhere('gateway_uuid', 'like', "%{$term}%")
                        ->orWhere('client_id', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('cms.payments.index', compact('payments', 'statuses'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['events' => fn ($q) => $q->orderBy('id')]);

        return view('cms.payments.show', compact('payment'));
    }
}
