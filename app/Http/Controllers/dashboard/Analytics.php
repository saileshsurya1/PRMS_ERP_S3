<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DailyActivityLog;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\Todo;
use App\Models\KpiTarget;
use App\Models\User;
use App\Services\SalesKpiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Analytics extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->isCustomer()) {
            return $this->customer($request);
        }

        $today = Carbon::today();

        // Fetch User and Team Todos (Active & Pending)
        $userTasks = Todo::query()
            ->when(!$user->isAdmin() && !$user->isOwner(), function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhere('assigned_to_id', $user->id);
                });
            })
            ->where('status', '!=', 'completed')
            ->with(['creator', 'assignedUser'])
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')
            ->take(10)
            ->get();

        // Fetch RFQ Action items
        $rfqScope = Rfq::query()->whereIn('current_status', ['follow_up', 'follow_through']);
        if (!$user->isAdmin() && !$user->isOwner()) {
            $rfqScope->where('sales_engineer_id', $user->id);
        }

        $rfqTodos = $rfqScope->with('customer')
            ->orderByRaw('CASE WHEN quotation_submission_target_date IS NULL THEN 1 ELSE 0 END, quotation_submission_target_date ASC')
            ->take(10)
            ->get();

        return view('content.dashboard.welcome', compact('user', 'today', 'userTasks', 'rfqTodos'));
    }

    public function owner(Request $request)
    {
        abort_unless($request->user()->isOwner() || $request->user()->isAdmin(), 403);
        $start = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $end = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();
        $engineerId = $request->integer('engineer_id') ?: null;
        $rfqs = Rfq::query()->when($engineerId, fn ($query) => $query->where('sales_engineer_id', $engineerId))->whereBetween('rfq_received_date', [$start, $end]);
        $customers = Customer::query()->when($engineerId, fn ($query) => $query->where('assigned_sales_engineer_id', $engineerId)->orWhere('sales_engineer_id', $engineerId))->whereBetween('created_at', [$start, $end]);
        $complaints = \App\Models\CustomerComplaint::query()->when($engineerId, fn ($query) => $query->where('sales_engineer_id', $engineerId))->whereBetween('reported_date', [$start, $end]);
        $stats = [
            'salesEngineers' => User::where('role', 'sales_engineer')->count(),
            'customers' => (clone $customers)->count(),
            'activeComplaints' => (clone $complaints)->whereIn('status', ['open', 'in_progress'])->count(),
            'rfqs' => (clone $rfqs)->count(),
            'won' => (clone $rfqs)->where('current_status', 'won')->count()
        ];
        $statusData = (clone $rfqs)->selectRaw('current_status, count(*) as total')->groupBy('current_status')->pluck('total', 'current_status');
        $engineers = User::where('role', 'sales_engineer')->orderBy('name')->get();
        $review = app(SalesKpiService::class)->ownerReview($engineerId, $start, $end);
        return view('content.dashboard.owner', compact('start', 'end', 'engineerId', 'engineers', 'stats', 'statusData', 'review'));
    }

    public function kpis(Request $request)
    {
        abort_unless($request->user()->isSalesEngineer() || $request->user()->isAdmin(), 403);
        return $this->salesDashboard($request);
    }

    private function salesDashboard(Request $request)
    {
        $user = $request->user();
        $start = Carbon::parse($request->input('from', now()->startOfMonth()->toDateString()))->startOfDay();
        $end = Carbon::parse($request->input('to', now()->toDateString()))->endOfDay();
        $scope = fn ($query) => ($user->isAdmin() || $user->isOwner()) ? $query : $query->where('sales_engineer_id', $user->id);

        $rfqs = $scope(Rfq::query());
        $monthlyRfqs = (clone $rfqs)->whereBetween('rfq_received_date', [$start, $end]);
        $booked = (clone $monthlyRfqs)->where('current_status', 'won')->sum('total_awarded_price');
        $quoted = (clone $monthlyRfqs)->sum('total_quoted_price');
        $pipeline = (clone $rfqs)->whereIn('current_status', ['follow_up', 'follow_through'])->sum('total_quoted_price');
        $customerQuery = ($user->isAdmin() || $user->isOwner()) ? Customer::query() : Customer::where(fn ($q) => $q->where('assigned_sales_engineer_id', $user->id)->orWhere('sales_engineer_id', $user->id));
        $newCustomers = (clone $customerQuery)->whereBetween('created_at', [$start, $end])->count();
        $quotationQuery = ($user->isAdmin() || $user->isOwner())
            ? Quotation::query()
            : Quotation::whereHas('rfq', fn ($query) => $query->where('sales_engineer_id', $user->id));
        $pendingQuotes = (clone $quotationQuery)->whereIn('status', ['draft', 'submitted', 'under_review'])->count();
        $conversion = $quoted > 0 ? round(((float) (clone $monthlyRfqs)->where('current_status', 'won')->sum('total_awarded_price') / $quoted) * 100, 1) : 0;
        $invoiced = (clone $rfqs)->sum('total_invoiced_price');
        $received = (clone $rfqs)->withSum('payments', 'amount')->get()->sum('payments_sum_amount');
        $outstanding = max((float) $invoiced - (float) $received, 0);
        $target = ($user->isAdmin() || $user->isOwner())
            ? 20000000
            : (float) (KpiTarget::where('sales_engineer_id', $user->id)->where('kpi_code', 'order_booking')->where('period_type', 'monthly')->where('valid_from', '<=', $start)->where(fn ($query) => $query->whereNull('valid_to')->orWhere('valid_to', '>=', $end))->latest('valid_from')->value('target_value') ?? 0);
        $achievement = $target > 0 ? round(($booked / $target) * 100, 1) : 0;
        $daily = DailyActivityLog::when(! $user->isAdmin() && ! $user->isOwner(), fn ($query) => $query->where('sales_engineer_id', $user->id))->whereBetween('activity_date', [$start, $end])->get();
        $critical = (clone $rfqs)->whereIn('current_status', ['follow_up', 'follow_through'])
            ->where(function ($query) use ($end) {
                $query->where('quotation_submission_target_date', '<=', $end->copy()->addDays(7))
                    ->orWhere('total_quoted_price', '>=', 500000);
            })->with('customer')->orderByDesc('total_quoted_price')->limit(5)->get();
        $risks = [];
        if ($achievement < 80) $risks[] = 'Order booking is below 80% of the monthly target.';
        if ($pipeline < 50000000) $risks[] = 'Active pipeline is below the ₹5 Crore threshold.';
        if ($conversion < 30) $risks[] = 'RFQ-to-order conversion is below 30%.';
        if ($outstanding > 0) $risks[] = 'Outstanding collections require follow-up.';
        if ($pendingQuotes > 0) $risks[] = $pendingQuotes . ' quotation(s) are still pending.';

        return view('content.dashboard.dashboards-analytics', compact('start', 'booked', 'target', 'achievement', 'pipeline', 'newCustomers', 'pendingQuotes', 'conversion', 'outstanding', 'critical', 'risks', 'daily', 'user'));
    }

    public function customer(Request $request)
    {
        abort_unless($request->user()->isCustomer() && $request->user()->customer_id, 403);
        $customer = Customer::with(['rfqs' => fn ($query) => $query->latest()->take(10), 'complaints' => fn ($query) => $query->latest()->take(10)])->findOrFail($request->user()->customer_id);
        return view('content.dashboard.customer', compact('customer'));
    }
}
