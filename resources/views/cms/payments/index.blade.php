@extends('voyager::master')

@section('page_title', 'Payments')

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-credit-cards"></i> Payments
    </h1>
@stop

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-body">
                    @if($failedPushes)
                        <div class="alert alert-danger clearfix">
                            <form method="POST" action="{{ route('admin.payments.repush-failed') }}" class="pull-right" style="margin:0"
                                  onsubmit="return confirm('Re-push all failed payments to core banking?')">
                                @csrf
                                <button class="btn btn-sm btn-danger" style="margin:0">Re-push all failed</button>
                            </form>
                            <strong>{{ $failedPushes }}</strong> paid payment(s) failed to reach core banking.
                            <a href="{{ route('admin.payments.index', ['push' => 'failed']) }}">Show them</a>
                        </div>
                    @endif
                    <form method="GET" class="form-inline" style="margin-bottom:15px">
                        <select name="status" class="form-control">
                            <option value="">— status —</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        <select name="push" class="form-control">
                            <option value="">— banking push —</option>
                            @foreach($pushStatuses as $s)
                                <option value="{{ $s }}" @selected(request('push') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        <select name="channel" class="form-control">
                            <option value="">— channel —</option>
                            <option value="app" @selected(request('channel') === 'app')>app</option>
                            <option value="web" @selected(request('channel') === 'web')>web</option>
                        </select>
                        <input type="text" name="q" class="form-control" placeholder="ref / loan / transaction / client" value="{{ request('q') }}">
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                        <button class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-default">Reset</a>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reference</th>
                                    <th>Status</th>
                                    <th>Channel</th>
                                    <th>Amount</th>
                                    <th>Loan</th>
                                    <th>Areeba transaction</th>
                                    <th>Banking push</th>
                                    <th>Created</th>
                                    <th>Paid at</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($payments as $p)
                                <tr>
                                    <td>{{ $p->id }}</td>
                                    <td><code>{{ $p->reference }}</code></td>
                                    <td><span class="label label-{{ $p->status === 'paid' ? 'success' : (in_array($p->status, \App\Models\Payment::OPEN_STATUSES) ? 'warning' : ($p->status === 'cancelled' ? 'default' : 'danger')) }}">{{ $p->status }}</span>
                                        @if($p->status_reason)<br><small class="text-muted">{{ \Illuminate\Support\Str::limit($p->status_reason, 60) }}</small>@endif
                                    </td>
                                    <td>{{ $p->channel }}</td>
                                    <td>{{ number_format((float) $p->amount, 0) }} {{ $p->currency }}</td>
                                    <td>{{ $p->loan_number }}</td>
                                    <td><small>{{ $p->areeba_transaction_id }}</small></td>
                                    <td>
                                        @if($p->banking_push_status)
                                            <span class="label label-{{ $p->banking_push_status === 'success' ? 'success' : ($p->banking_push_status === 'failed' ? 'danger' : 'warning') }}">{{ $p->banking_push_status }}</span>
                                            @if($p->banking_push_attempts)<small class="text-muted">×{{ $p->banking_push_attempts }}</small>@endif
                                        @endif
                                    </td>
                                    <td>{{ optional($p->created_at)->format('Y-m-d H:i') }}</td>
                                    <td>{{ optional($p->paid_at)->format('Y-m-d H:i') }}</td>
                                    <td><a href="{{ route('admin.payments.show', $p) }}" class="btn btn-sm btn-primary">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="11" class="text-center">No payments found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop
