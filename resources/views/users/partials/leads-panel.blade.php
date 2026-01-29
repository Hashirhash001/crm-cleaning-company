@if($leads->count() > 0)
<div class="table-responsive">
    <table class="table table-hover table-sm">
        <thead>
            <tr>
                <th>Lead</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Services</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leads as $lead)
            <tr>
                <td><span class="badge bg-primary">{{ $lead->lead_code }}</span></td>
                <td>{{ $lead->name }}</td>
                <td><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></td>
                <td>
                    @if($lead->services->count() > 0)
                        <small>{{ $lead->services->pluck('name')->take(2)->join(', ') }}
                        @if($lead->services->count() > 2)
                            <span class="badge bg-secondary">+{{ $lead->services->count() - 2 }}</span>
                        @endif
                        </small>
                    @endif
                </td>
                <td>₹{{ number_format($lead->amount, 0) }}</td>
                <td>
                    @if($lead->status === 'approved')
                        <span class="badge bg-success">Approved</span>
                    @elseif($lead->status === 'confirmed')
                        <span class="badge bg-info">Confirmed</span>
                    @elseif($lead->status === 'rejected')
                        <span class="badge bg-danger">Rejected</span>
                    @else
                        <span class="badge bg-warning">{{ ucfirst($lead->status) }}</span>
                    @endif
                </td>
                <td><small>{{ $lead->created_at->format('d M Y') }}</small></td>
                <td>
                    <a href="{{ route('leads.show', $lead->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="las la-external-link-alt"></i>
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $leads->links('pagination::bootstrap-5') }}
@else
<div class="text-center py-5">
    <i class="las la-inbox" style="font-size: 4rem; color: #cbd5e0;"></i>
    <p class="text-muted mt-3">No leads found</p>
</div>
@endif
