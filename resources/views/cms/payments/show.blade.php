@extends('voyager::master')

@section('page_title', 'Payment ' . $payment->reference)

@php
    $json = fn ($value) => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $statusLabel = $payment->status === 'paid' ? 'success' : (in_array($payment->status, \App\Models\Payment::OPEN_STATUSES) ? 'warning' : ($payment->status === 'cancelled' ? 'default' : 'danger'));
    $pushLabel = ['success' => 'success', 'failed' => 'danger', 'pending' => 'warning'][$payment->banking_push_status] ?? 'default';
@endphp

@section('page_header')
    <h1 class="page-title"><i class="voyager-credit-cards"></i> Payment {{ $payment->reference }}</h1>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-default">&larr; Back</a>
    @if(!$payment->isPaid() && $payment->areeba_transaction_id)
        <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" style="display:inline">
            @csrf
            <button class="btn btn-info">Re-verify with Areeba</button>
        </form>
    @endif
@stop

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-5">
            <div class="panel panel-bordered">
                <div class="panel-heading"><h3 class="panel-title">Payment</h3></div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr><th>Status</th><td><span class="label label-{{ $statusLabel }}">{{ $payment->status }}</span></td></tr>
                            <tr><th>Status reason</th><td>{{ $payment->status_reason }}</td></tr>
                            <tr><th>Amount</th><td>{{ number_format((float) $payment->amount, 0) }} {{ $payment->currency }}</td></tr>
                            <tr><th>Channel</th><td>{{ $payment->channel }} ({{ $payment->locale }})</td></tr>
                            <tr><th>Loan number</th><td>{{ $payment->loan_number }}</td></tr>
                            <tr><th>Borrower</th><td>{{ $payment->customer_name }}</td></tr>
                            <tr><th>Phone</th><td>{{ $payment->phone_number }}</td></tr>
                            <tr><th>Branch</th><td>{{ $payment->branch_name ?: $payment->branch_code }}</td></tr>
                            <tr><th>Loan product</th><td>{{ $payment->loan_product }}</td></tr>
                            <tr><th>Due date</th><td>{{ optional($payment->due_date)->format('Y-m-d') }}</td></tr>
                            <tr><th>Client ID</th><td>{{ $payment->client_id }}</td></tr>
                            <tr><th>Areeba transaction</th><td>{{ $payment->areeba_transaction_id ?? '—' }}</td></tr>
                            <tr><th>Created</th><td>{{ $payment->created_at }}</td></tr>
                            <tr><th>Paid at</th><td>{{ $payment->paid_at ?? '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel panel-bordered">
                <div class="panel-heading"><h3 class="panel-title">Core banking push</h3></div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr><th>Push status</th><td><span class="label label-{{ $pushLabel }}">{{ $payment->banking_push_status ?? 'not applicable' }}</span></td></tr>
                            <tr><th>Status code</th><td>{{ $payment->banking_status_code }}</td></tr>
                            <tr><th>Action status</th><td>{{ $payment->banking_action_status }}</td></tr>
                            <tr><th>Attempts</th><td>{{ $payment->banking_push_attempts }}</td></tr>
                            <tr><th>Last attempt</th><td>{{ $payment->banking_last_attempt_at ?? '—' }}</td></tr>
                            <tr><th>Pushed at</th><td>{{ $payment->banking_pushed_at ?? '—' }}</td></tr>
                        </tbody>
                    </table>
                    @if($payment->isPaid() && $payment->banking_push_status !== 'success')
                        <form method="POST" action="{{ route('admin.payments.repush', $payment) }}"
                              onsubmit="return confirm('Push this payment to core banking now?')">
                            @csrf
                            <button class="btn btn-danger">Re-push to core banking</button>
                        </form>
                    @endif
                    @if($payment->banking_response_json)
                        <details>
                            <summary><small class="text-muted">core banking response</small></summary>
                            <pre style="max-height:240px;overflow:auto">{{ $json($payment->banking_response_json) }}</pre>
                        </details>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="panel panel-bordered">
                <div class="panel-heading"><h3 class="panel-title">Event timeline</h3></div>
                <div class="panel-body">
                    <table class="table table-hover">
                        <thead><tr><th>Time</th><th>Type</th><th>HTTP</th><th>Message</th></tr></thead>
                        <tbody>
                        @forelse($payment->events as $e)
                            <tr class="{{ $e->type === 'alert' ? 'danger' : '' }}">
                                <td><small>{{ $e->created_at }}</small></td>
                                <td><code>{{ $e->type }}</code></td>
                                <td>{{ $e->http_status }}</td>
                                <td>
                                    {{ $e->message }}
                                    @if($e->context)
                                        <details>
                                            <summary><small class="text-muted">context</small></summary>
                                            <pre style="max-height:240px;overflow:auto">{{ $json($e->context) }}</pre>
                                        </details>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No events.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="panel panel-bordered">
                <div class="panel-heading"><h3 class="panel-title">API calls</h3></div>
                <div class="panel-body">
                    <table class="table table-hover">
                        <thead><tr><th>Time</th><th>Service</th><th>Action</th><th>HTTP</th><th>ms</th><th></th></tr></thead>
                        <tbody>
                        @forelse($payment->apiLogs as $log)
                            <tr class="{{ $log->error || $log->http_status >= 400 ? 'danger' : '' }}">
                                <td><small>{{ $log->created_at }}</small></td>
                                <td>{{ $log->service_name }}</td>
                                <td><code>{{ $log->action }}</code></td>
                                <td>{{ $log->http_status ?? '—' }}</td>
                                <td>{{ $log->execution_time_ms }}</td>
                                <td>
                                    <details>
                                        <summary><small class="text-muted">{{ $log->method }} {{ \Illuminate\Support\Str::limit($log->endpoint, 60) }}</small></summary>
                                        @if($log->error)<p class="text-danger">{{ $log->error }}</p>@endif
                                        <small class="text-muted">request</small>
                                        <pre style="max-height:200px;overflow:auto">{{ $json($log->request_payload) }}</pre>
                                        <small class="text-muted">response</small>
                                        <pre style="max-height:240px;overflow:auto">{{ is_array($log->response_decoded) ? $json($log->response_decoded) : $log->response_payload }}</pre>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No API calls logged.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($payment->areeba_response_json)
                <div class="panel panel-bordered">
                    <div class="panel-heading"><h3 class="panel-title">Raw Areeba responses</h3></div>
                    <div class="panel-body">
                        <pre style="max-height:320px;overflow:auto">{{ $json($payment->areeba_response_json) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@stop
