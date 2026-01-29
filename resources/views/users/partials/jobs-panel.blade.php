@if($jobs->count() > 0)
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th>Work Order</th>
                <th>Customer</th>
                <th>Services</th>
                <th>Amount</th>
                <th>Balance</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobs as $job)
            <tr>
                <td><span class="badge bg-primary">{{ $job->job_code }}</span></td>
                <td>
                    @if($job->customer)
                        <strong>{{ $job->customer->name }}</strong><br>
                        <small class="text-muted">{{ $job->customer->phone }}</small>
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($job->services->count() > 0)
                        <small>{{ $job->services->pluck('name')->take(2)->join(', ') }}
                        @if($job->services->count() > 2)
                            <span class="badge bg-secondary">+{{ $job->services->count() - 2 }}</span>
                        @endif
                        </small>
                    @endif
                </td>
                <td>₹{{ number_format($job->amount, 0) }}</td>
                <td>
                    @php $balance = $job->amount - $job->amount_paid; @endphp
                    <span class="badge {{ $balance > 0 ? 'bg-danger' : 'bg-success' }}">
                        ₹{{ number_format($balance, 0) }}
                    </span>
                </td>
                <td>
                    @if($job->status === 'completed')
                        <span class="badge bg-success">Completed</span>
                    @elseif($job->status === 'approved')
                        <span class="badge bg-info">Approved</span>
                    @elseif($job->status === 'confirmed')
                        <span class="badge bg-primary">Confirmed</span>
                    @else
                        <span class="badge bg-warning">{{ ucfirst($job->status) }}</span>
                    @endif
                </td>
                <td><small>{{ $job->scheduled_date ? $job->scheduled_date->format('d M Y') : 'Not Set' }}</small></td>
                <td>
                    <a href="{{ route('jobs.show', $job->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="las la-external-link-alt"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $jobs->links('pagination::bootstrap-5') }}
@else
<div class="text-center py-5">
    <i class="las la-inbox" style="font-size: 4rem; color: #cbd5e0;"></i>
    <p class="text-muted mt-3">No records found</p>
</div>
@endif
