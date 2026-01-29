@if($jobs->count() > 0)
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th>Work Order</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Amount</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobs as $job)
            @php $due = $job->amount - $job->amount_paid; @endphp
            <tr>
                <td><span class="badge bg-primary">{{ $job->job_code }}</span></td>
                <td>
                    @if($job->customer)
                        <strong>{{ $job->customer->name }}</strong>
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($job->customer && $job->customer->phone)
                        <a href="tel:{{ $job->customer->phone }}" class="btn btn-sm btn-success">
                            <i class="las la-phone"></i> {{ $job->customer->phone }}
                        </a>
                    @else
                        N/A
                    @endif
                </td>
                <td>₹{{ number_format($job->amount, 0) }}</td>
                <td>₹{{ number_format($job->amount_paid, 0) }}</td>
                <td><span class="badge bg-danger fs-6">₹{{ number_format($due, 0) }}</span></td>
                <td>
                    <a href="{{ route('jobs.show', $job->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="las la-external-link-alt"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light">
            <tr>
                <th colspan="3" class="text-end">Total:</th>
                <th>₹{{ number_format($jobs->sum('amount'), 0) }}</th>
                <th>₹{{ number_format($jobs->sum('amount_paid'), 0) }}</th>
                <th><strong class="text-danger">₹{{ number_format($jobs->sum(function($j) { return $j->amount - $j->amount_paid; }), 0) }}</strong></th>
                <th></th>
            </tr>
        </tfoot>
    </table>
</div>

{{ $jobs->links('pagination::bootstrap-5') }}
@else
<div class="text-center py-5">
    <i class="las la-inbox" style="font-size: 4rem; color: #cbd5e0;"></i>
    <p class="text-muted mt-3">No pending payments</p>
</div>
@endif
