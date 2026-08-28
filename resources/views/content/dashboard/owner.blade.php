@extends('layouts/layoutMaster')
@section('title', 'Owner Sales Review')
@section('vendor-script')<script src="{{ asset('assets/vendor/libs/chartjs/chartjs.js') }}"></script>@endsection
@section('content')
@php
  $currency = fn ($value) => '₹ ' . number_format((float) $value, 2);
  $totals = $review['totals'];
  $incentive = $totals['incentive'];
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4"><div><span class="text-primary fw-semibold text-uppercase small">Owner only</span><h4 class="mb-1">Sales review</h4><p class="text-muted mb-0">KPI, activity, and incentive for the selected period.</p></div><form class="d-flex gap-2" method="GET"><input class="form-control" type="date" name="from" value="{{ $start->toDateString() }}"><input class="form-control" type="date" name="to" value="{{ $end->toDateString() }}"><select class="form-select" name="engineer_id"><option value="">All sales engineers</option>@foreach($engineers as $engineer)<option value="{{ $engineer->id }}" @selected($engineerId === $engineer->id)>{{ $engineer->name }}</option>@endforeach</select><button class="btn btn-primary">Filter</button></form></div>

<div class="row g-4 mb-4">
  <div class="col-sm-6 col-xl"><div class="card h-100"><div class="card-body"><small class="text-muted">Monthly target</small><h4 class="mt-2 mb-0">{{ $currency($totals['monthly_target']) }}</h4></div></div></div>
  <div class="col-sm-6 col-xl"><div class="card h-100"><div class="card-body"><small class="text-muted">Actual order booking</small><h4 class="mt-2 mb-0">{{ $currency($totals['order_booking']) }}</h4></div></div></div>
  <div class="col-sm-6 col-xl"><div class="card h-100"><div class="card-body"><small class="text-muted">Achievement</small><h4 class="mt-2 mb-1">{{ $totals['achievement'] }}%</h4><div class="progress" style="height: 6px"><div class="progress-bar" style="width: {{ min($totals['achievement'], 100) }}%"></div></div></div></div></div>
  <div class="col-sm-6 col-xl"><div class="card h-100"><div class="card-body"><small class="text-muted">KPI score</small><h4 class="mt-2 mb-0">{{ number_format($totals['kpi_score'], 2) }}</h4></div></div></div>
  <div class="col-sm-6 col-xl"><div class="card h-100"><div class="card-body"><small class="text-muted">Incentive</small><h4 class="mt-2 mb-0">{{ $currency($incentive['final_incentive_amount']) }}</h4><small class="text-muted">{{ $incentive['slab_label'] }}</small></div></div></div>
</div>

<div class="row g-4 mb-4">
  @foreach([
    'customer_calls' => 'Customer calls',
    'follow_up_calls' => 'Follow-up calls',
    'customer_visits' => 'Customer visits',
    'online_meetings' => 'Online meetings',
    'rfqs_received' => 'RFQs received',
    'quotations_submitted' => 'Quotations submitted',
  ] as $key => $label)
    <div class="col-sm-6 col-xl-2"><div class="card h-100"><div class="card-body"><small class="text-muted">{{ $label }}</small><h3 class="mt-2 mb-0">{{ $totals[$key] }}</h3></div></div></div>
  @endforeach
</div>

<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Engineer KPI review</h5><small class="text-muted">Incentive = base × slab (0× below 80%, 1× at 80–99%, 1.5× at 100–110%, 2× above 110%)</small></div>
  <div class="table-responsive">
    <table class="table mb-0 align-middle">
      <thead>
        <tr>
          <th>Sales engineer</th>
          <th>Monthly target</th>
          <th>Order booking</th>
          <th>Achievement</th>
          <th>Calls</th>
          <th>Follow-ups</th>
          <th>Visits</th>
          <th>Meetings</th>
          <th>RFQs</th>
          <th>Quotations</th>
          <th>KPI score</th>
          <th>Incentive</th>
        </tr>
      </thead>
      <tbody>
        @forelse($review['rows'] as $row)
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <img src="{{ $row['engineer_photo'] ?? asset('assets/img/avatars/1.png') }}" class="rounded-circle" width="32" height="32" alt="{{ $row['engineer_name'] }}" style="object-fit: cover;">
                <span class="fw-medium">{{ $row['engineer_name'] }}</span>
              </div>
            </td>
            <td>{{ $currency($row['monthly_target']) }}</td>
            <td>{{ $currency($row['order_booking']) }}</td>
            <td><span class="badge {{ $row['achievement'] >= 100 ? 'bg-label-success' : ($row['achievement'] >= 80 ? 'bg-label-warning' : 'bg-label-danger') }}">{{ $row['achievement'] }}%</span></td>
            <td>{{ $row['customer_calls'] }}</td>
            <td>{{ $row['follow_up_calls'] }}</td>
            <td>{{ $row['customer_visits'] }}</td>
            <td>{{ $row['online_meetings'] }}</td>
            <td>{{ $row['rfqs_received'] }}</td>
            <td>{{ $row['quotations_submitted'] }}</td>
            <td>{{ number_format($row['kpi_score'], 2) }}</td>
            <td>{{ $currency($row['incentive']['final_incentive_amount']) }}<div class="small text-muted">{{ $row['incentive']['slab_label'] }} @if($row['incentive']['multiplier'] > 0)× {{ $row['incentive']['multiplier'] }}@endif</div></td>
          </tr>
        @empty
          <tr><td colspan="12" class="text-center text-muted py-4">No sales engineers in this review.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @include('partials.pagination', ['paginator' => $review['rows']])
</div>

<div class="row g-4 mb-4">@foreach(['salesEngineers' => 'Sales engineers','customers' => 'Customers','activeComplaints' => 'Active complaints','rfqs' => 'RFQs','won' => 'Won orders'] as $key => $label)<div class="col-sm-6 col-xl"><div class="card h-100"><div class="card-body"><small class="text-muted">{{ $label }}</small><h3 class="mt-2 mb-0">{{ $stats[$key] }}</h3></div></div></div>@endforeach</div>
<div class="row g-4"><div class="col-lg-6"><div class="card"><div class="card-header"><h5 class="mb-0">RFQ status mix</h5></div><div class="card-body"><canvas id="statusChart" height="220"></canvas></div></div></div><div class="col-lg-6"><div class="card"><div class="card-header"><h5 class="mb-0">Incentive calculation</h5></div><div class="card-body">
  @if($review['rows']->count() === 1)
    @php($row = $review['rows']->first())
    <div class="d-flex justify-content-between py-2"><span>Order booking achievement</span><strong>{{ $row['achievement'] }}%</strong></div>
    <div class="d-flex justify-content-between py-2"><span>Slab</span><strong>{{ $row['incentive']['slab_label'] }}</strong></div>
    <div class="d-flex justify-content-between py-2"><span>Multiplier</span><strong>{{ $row['incentive']['multiplier'] }}×</strong></div>
    <div class="d-flex justify-content-between py-2"><span>Base incentive</span><strong>{{ $currency($row['incentive']['base_incentive_amount']) }}</strong></div>
    <div class="d-flex justify-content-between py-2 border-top mt-2 pt-3"><span>Payable (base × multiplier)</span><strong>{{ $currency($row['incentive']['final_incentive_amount']) }}</strong></div>
  @else
    <div class="d-flex justify-content-between py-2"><span>Combined achievement</span><strong>{{ $totals['achievement'] }}%</strong></div>
    <div class="d-flex justify-content-between py-2"><span>Engineers reviewed</span><strong>{{ $review['rows']->count() }}</strong></div>
    <div class="d-flex justify-content-between py-2 border-top mt-2 pt-3"><span>Payable incentive</span><strong>{{ $currency($incentive['final_incentive_amount']) }}</strong></div>
    <p class="text-muted small mb-0 mt-3">Each engineer is scored on their own booking achievement. Payable is the sum of those incentives, not a company-wide slab.</p>
  @endif
  <p class="text-muted small mb-0 mt-3">Slabs: below 80% = 0×, 80–99% = 1×, 100–110% = 1.5×, above 110% = 2×.</p>
</div></div></div></div>
@endsection
@section('page-script')<script>new Chart(document.getElementById('statusChart'), {type:'doughnut', data:{labels:@json($statusData->keys()), datasets:[{data:@json($statusData->values()), backgroundColor:['#696cff','#03c3ec','#71dd37','#ffab00','#ff3e1d','#8592a3']}]}, options:{plugins:{legend:{position:'bottom'}}}});</script>@endsection
