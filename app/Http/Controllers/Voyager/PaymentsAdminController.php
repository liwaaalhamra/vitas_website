<?php

namespace App\Http\Controllers\Voyager;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Areeba\AreebaPaymentService;
use Illuminate\Http\Request;

/**
 * Back-office for gateway payments: list / filter, full timeline with raw
 * Areeba + core banking responses, and re-push of paid payments that did not
 * reach the core banking system. Follows the hand-rolled admin pattern of
 * Voyager\PushController (guarded by admin.user in routes/web.php).
 */
class PaymentsAdminController extends Controller
{
    public function __construct()
    {
        // Skip the base admin-section bootstrapping; this screen does not use $this->sections.
    }

    public function index(Request $request)
    {
        $statuses = array_merge(Payment::OPEN_STATUSES, Payment::TERMINAL_STATUSES);
        $pushStatuses = [Payment::PUSH_PENDING, Payment::PUSH_SUCCESS, Payment::PUSH_FAILED];

        $payments = Payment::query()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('push'), fn ($q) => $q->where('banking_push_status', $request->input('push')))
            ->when($request->filled('channel'), fn ($q) => $q->where('channel', $request->input('channel')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->input('q'));
                $q->where(function ($sub) use ($term) {
                    $sub->where('reference', 'like', "%{$term}%")
                        ->orWhere('loan_number', 'like', "%{$term}%")
                        ->orWhere('areeba_transaction_id', 'like', "%{$term}%")
                        ->orWhere('client_id', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $failedPushes = Payment::where('status', 'paid')->where('banking_push_status', Payment::PUSH_FAILED)->count();

        return view('cms.payments.index', compact('payments', 'statuses', 'pushStatuses', 'failedPushes'));
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'events'  => fn ($q) => $q->orderBy('id'),
            'apiLogs' => fn ($q) => $q->orderBy('id'),
        ]);

        return view('cms.payments.show', compact('payment'));
    }

    public function repush(Payment $payment, AreebaPaymentService $areeba)
    {
        if (!$payment->isPaid()) {
            return back()->with(['message' => 'Only paid payments can be pushed to core banking.', 'alert-type' => 'error']);
        }
        if ($payment->banking_push_status === Payment::PUSH_SUCCESS) {
            return back()->with(['message' => 'This payment was already accepted by core banking.', 'alert-type' => 'info']);
        }

        $payment = $areeba->pushToBanking($payment, $this->adminName());

        return back()->with($payment->banking_push_status === Payment::PUSH_SUCCESS
            ? ['message' => 'Pushed to core banking successfully.', 'alert-type' => 'success']
            : ['message' => 'Core banking push failed: '.($payment->banking_action_status ?: 'no response'), 'alert-type' => 'error']);
    }

    public function repushFailed(AreebaPaymentService $areeba)
    {
        $ok = 0;
        $failed = 0;

        Payment::where('status', 'paid')
            ->where('banking_push_status', Payment::PUSH_FAILED)
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->each(function (Payment $payment) use ($areeba, &$ok, &$failed) {
                $payment = $areeba->pushToBanking($payment, $this->adminName());
                $payment->banking_push_status === Payment::PUSH_SUCCESS ? $ok++ : $failed++;
            });

        return back()->with([
            'message'    => "Re-push finished: {$ok} succeeded, {$failed} still failing.",
            'alert-type' => $failed ? 'warning' : 'success',
        ]);
    }

    public function verify(Payment $payment, AreebaPaymentService $areeba)
    {
        if (!$payment->areeba_transaction_id) {
            return back()->with(['message' => 'No Areeba transaction id yet, nothing to verify.', 'alert-type' => 'warning']);
        }

        $payment = $areeba->verify($payment, null, $this->adminName());

        return back()->with(['message' => 'Verified with Areeba, status: '.$payment->status, 'alert-type' => 'info']);
    }

    private function adminName(): string
    {
        $user = auth()->user();

        return 'admin '.($user->email ?? $user->name ?? $user->id ?? 'unknown');
    }
}
