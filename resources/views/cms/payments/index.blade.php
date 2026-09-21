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
                    <form method="GET" class="form-inline" style="margin-bottom:15px">
                        <select name="status" class="form-control">
                            <option value="">— status —</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        <select name="channel" class="form-control">
                            <option value="">— channel —</option>
                            <option value="app" @selected(request('channel') === 'app')>app</option>
                            <option value="web" @selected(request('channel') === 'web')>web</option>
                        </select>
                        <input type="text" name="q" class="form-control" placeholder="ref / loan / uuid / client" value="{{ request('q') }}">
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
                                    <th>Client</th>
                                    <th>Method</th>
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
                                    <td><span class="label label-{{ $p->status === 'paid' ? 'success' : (in_array($p->status, ['pending','redirected']) ? 'warning' : 'danger') }}">{{ $p->status }}</span>
                                        @if($p->status_reason)<br><small class="text-muted">{{ \Illuminate\Support\Str::limit($p->status_reason, 60) }}</small>@endif
                                    </td>
                                    <td>{{ $p->channel }}</td>
                                    <td>{{ number_format((float) $p->amount, 2) }} {{ $p->currency }}</td>
                                    <td>{{ $p->loan_number }}</td>
                                    <td>{{ $p->client_id }}</td>
                                    <td>{{ $p->payment_method }}</td>
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
