@extends('layouts/layoutMaster')

@section('title', 'PRMS Sales Dashboard')

@section('content')
@php($currency = fn ($value) => '₹ ' . number_format((float) $value, 2))
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
  <div><span class="text-primary fw-semibold text-uppercase small">Project Review Management System</span><h4 class="mb-1">{{ $user->isOwner() || $user->isAdmin() ? 'Owner Dashboard' : 'My Sales Dashboard' }}</h4><p class="text-muted mb-0">{{ $start->format('F Y') }} performance and pipeline review</p></div>
  <div class="d-flex gap-2"><a class="btn btn-primary" href="{{ route('sales.rfqs') }}"><i class="mdi mdi-plus me-1"></i>New RFQ</a><a class="btn btn-outline-primary" href="{{ route('sales.daily-log') }}"><i class="mdi mdi-calendar-check-outline me-1"></i>Daily KPI</a></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="d-flex justify-content-between"><span class="text-muted">Monthly target</span><i class="mdi mdi-target text-primary mdi-24px"></i></div><h4 class="mt-3 mb-1">{{ $currency($target) }}</h4><small class="text-muted">{{ $user->isOwner() || $user->isAdmin() ? 'Company target' : 'Personal target' }}</small></div></div></div>
  <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="d-flex justify-content-between"><span class="text-muted">Orders booked</span><i class="mdi mdi-cash-check text-success mdi-24px"></i></div><h4 class="mt-3 mb-1">{{ $currency($booked) }}</h4><span class="badge bg-label-success">{{ $achievement }}% achieved</span></div></div></div>
  <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="d-flex justify-content-between"><span class="text-muted">Active pipeline</span><i class="mdi mdi-chart-line text-info mdi-24px"></i></div><h4 class="mt-3 mb-1">{{ $currency($pipeline) }}</h4><small class="text-muted">Minimum target ₹ 5 Crore</small></div></div></div>
  <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="d-flex justify-content-between"><span class="text-muted">Outstanding collections</span><i class="mdi mdi-cash-alert text-danger mdi-24px"></i></div><h4 class="mt-3 mb-1">{{ $currency($outstanding) }}</h4><small class="text-muted">Based on recorded payments</small></div></div></div>
</div>
<div class="row g-4 mb-4">
  <div class="col-xl-8"><div class="card h-100"><div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Monthly progress</h5><span class="text-muted">{{ $achievement }}%</span></div><div class="card-body"><div class="progress" style="height: 12px"><div class="progress-bar bg-primary" style="width: {{ min($achievement, 100) }}%" role="progressbar"></div></div><div class="d-flex justify-content-between mt-2"><small>{{ $currency($booked) }} booked</small><small>{{ $currency($target) }} target</small></div><div class="row g-3 mt-3"><div class="col-md-4"><div class="border rounded p-3"><small class="text-muted d-block">New customers</small><strong class="fs-4">{{ $newCustomers }}</strong></div></div><div class="col-md-4"><div class="border rounded p-3"><small class="text-muted d-block">Quotations pending</small><strong class="fs-4">{{ $pendingQuotes }}</strong></div></div><div class="col-md-4"><div class="border rounded p-3"><small class="text-muted d-block">Conversion rate</small><strong class="fs-4">{{ $conversion }}%</strong></div></div></div></div></div></div>
  <div class="col-xl-4"><div class="card h-100"><div class="card-header"><h5 class="mb-0">Daily activity</h5></div><div class="card-body"><div class="d-flex justify-content-between py-2"><span>Customer calls</span><strong>{{ $daily->sum('customer_calls') }} / 15 daily</strong></div><div class="d-flex justify-content-between py-2"><span>Follow-up calls</span><strong>{{ $daily->sum('follow_up_calls') }} / 20 daily</strong></div><div class="d-flex justify-content-between py-2"><span>Visits</span><strong>{{ $daily->sum('customer_visits') }} / 2 daily</strong></div><div class="d-flex justify-content-between py-2"><span>Online meetings</span><strong>{{ $daily->sum('online_meetings') }} / 2 daily</strong></div><div class="d-flex justify-content-between py-2"><span>CRM updates</span><strong>{{ $daily->where('crm_updated', true)->count() }} days</strong></div><a href="{{ route('sales.daily-log') }}" class="btn btn-outline-primary w-100 mt-3">Open KPI log</a></div></div></div>
</div>
<div class="row g-4">
  <div class="col-xl-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Critical opportunities</h5>
        <a href="{{ route('sales.rfqs') }}">View RFQs</a>
      </div>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>RFQ</th>
              <th>Customer</th>
              <th>Value</th>
              <th>Due date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($critical as $rfq)
              <tr>
                <td>{{ $rfq->rfq_number }}</td>
                <td>{{ $rfq->customer->company_name }}</td>
                <td>{{ $currency($rfq->total_quoted_price) }}</td>
                <td>{{ $rfq->quotation_submission_target_date?->format('d M Y') ?: 'Not set' }}</td>
                <td><span class="badge bg-label-warning">{{ ucfirst(str_replace('_', ' ', $rfq->current_status)) }}</span></td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-4">No critical opportunities</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      @include('partials.pagination', ['paginator' => $critical])
    </div>
  </div>
  <div class="col-xl-4"><div class="card h-100"><div class="card-header"><h5 class="mb-0">Risks affecting target</h5></div><div class="card-body">@forelse($risks as $risk)<div class="d-flex gap-2 mb-3"><i class="mdi mdi-alert-circle-outline text-danger mdi-20px"></i><span>{{ $risk }}</span></div>@empty<div class="text-success"><i class="mdi mdi-check-circle-outline me-1"></i>No active risks</div>@endforelse</div></div></div>
</div>
@endsection
