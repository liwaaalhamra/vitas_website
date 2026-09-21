@extends('voyager::master')

@section('page_title', 'Payment ' . $payment->reference)

@section('page_header')
    <h1 class="page-title"><i class="voyager-credit-cards"></i> Payment {{ $payment->reference }}</h1>
    <a href="{{ route('admin.payments.index') }}" class="btn btn-default">&larr; Back</a>
@stop

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-5">
            <div class="panel panel-bordered">
                <div class="panel-heading"><h3 class="panel-title">Summary</h3></div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr><th>Status</th><td><span class="label label-{{ $payment->status === 'paid' ? 'success' : (in_array($payment->status, ['pending','redirected']) ? 'warning' : 'danger') }}">{{ $payment->status }}</span></td></tr>
                            <tr><th>Status reason</th><td>{{ $payment->status_reason }}</td></tr>
                            <tr><th>Channel</th><td>{{ $payment->channel }}</td></tr>
                            <tr><th>Amount</th><td>{{ number_format((float) $payment->amount, 2) }} {{ $payment->currency }}</td></tr>
                            <tr><th>USD / IQD</th><td>{{ $payment->amount_usd }} / {{ $payment->amount_iqd }} (rate {{ $payment->exchange_rate }})</td></tr>
                            <tr><th>Loan number</th><td>{{ $payment->loan_number }}</td></tr>
                            <tr><th>Installment</th><td>{{ $payment->installment_number }}</td></tr>
                            <tr><th>Branch</th><td>{{ $payment->branch_code }}</td></tr>
                            <tr><th>Client ID</th><td>{{ $payment->client_id }}</td></tr>
                            <tr><th>App user ID</th><td>{{ $payment->app_user_id }}</td></tr>
                            <tr><th>Phone</th><td>{{ $payment->phone_number }}</td></tr>
                            <tr><th>Gateway UUID</th><td>{{ $payment->gateway_uuid }}</td></tr>
                            <tr><th>Purchase ID</th><td>{{ $payment->gateway_purchase_id }}</td></tr>
                            <tr><th>Payment method</th><td>{{ $payment->payment_method }}</td></tr>
                            <tr><th>Created</th><td>{{ $payment->created_at }}</td></tr>
                            <tr><th>Paid at</th><td>{{ $payment->paid_at }}</td></tr>
                            <tr><th>Core confirmed at</th><td>{{ $payment->confirmed_at ?? '—' }} <small class="text-muted">({{ $payment->confirm_attempts }} attempt(s))</small></td></tr>
                            <tr><th>Confirm error</th><td>{{ $payment->confirm_error }}</td></tr>
                        </tbody>
                    </table>
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
                            <tr>
                                <td><small>{{ $e->created_at }}</small></td>
                                <td><code>{{ $e->type }}</code></td>
                                <td>{{ $e->http_status }}</td>
                                <td>
                                    {{ $e->message }}
                                    @if($e->context)
                                        <details>
                                            <summary><small class="text-muted">context</small></summary>
                                            <pre style="max-height:240px;overflow:auto">{{ json_encode($e->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
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

            @if($payment->meta)
                <div class="panel panel-bordered">
                    <div class="panel-heading"><h3 class="panel-title">Meta</h3></div>
                    <div class="panel-body">
                        <pre style="max-height:320px;overflow:auto">{{ json_encode($payment->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@stop
