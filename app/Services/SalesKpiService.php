<?php

namespace App\Services;

use App\Models\DailyActivityLog;
use App\Models\Incentive;
use App\Models\KpiActual;
use App\Models\KpiTarget;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalesKpiService
{
    public function calculate(User $engineer, KpiTarget $target, Carbon $start, Carbon $end): KpiActual
    {
        $actual = match ($target->kpi_code) {
            'order_booking' => (float) Rfq::where('sales_engineer_id', $engineer->id)->where('current_status', 'won')->whereBetween('rfq_received_date', [$start, $end])->sum('total_awarded_price'),
            'new_rfqs' => Rfq::where('sales_engineer_id', $engineer->id)->whereBetween('rfq_received_date', [$start, $end])->count(),
            'customer_visits' => DailyActivityLog::where('sales_engineer_id', $engineer->id)->whereBetween('activity_date', [$start, $end])->sum('customer_visits'),
            'customer_calls' => DailyActivityLog::where('sales_engineer_id', $engineer->id)->whereBetween('activity_date', [$start, $end])->sum('customer_calls'),
            'follow_up_calls' => DailyActivityLog::where('sales_engineer_id', $engineer->id)->whereBetween('activity_date', [$start, $end])->sum('follow_up_calls'),
            default => 0,
        };
        $achievement = $target->target_value > 0 ? ($actual / $target->target_value) * 100 : 0;
        return KpiActual::updateOrCreate(
            ['sales_engineer_id' => $engineer->id, 'kpi_target_id' => $target->id, 'period_start' => $start->toDateString(), 'period_end' => $end->toDateString()],
            ['actual_value' => $actual, 'achievement_percentage' => round($achievement, 2), 'weighted_score' => round(min($achievement, 100) * $target->weight_percentage / 100, 2), 'calculated_at' => now()]
        );
    }

    public function incentivePreview(float $achievement, ?float $base = null): array
    {
        [$slab, $multiplier] = match (true) {
            $achievement < 80 => ['no_incentive', 0],
            $achievement < 100 => ['standard', 1],
            $achievement <= 110 => ['one_point_five_x', 1.5],
            default => ['two_x_recognition', 2],
        };
        $base ??= (float) config('prms.standard_incentive_amount', 0);
        $labels = [
            'no_incentive' => 'No incentive',
            'standard' => 'Standard (1×)',
            'one_point_five_x' => '1.5×',
            'two_x_recognition' => '2× recognition',
        ];

        return [
            'slab' => $slab,
            'slab_label' => $labels[$slab],
            'base_incentive_amount' => $base,
            'multiplier' => $multiplier,
            'final_incentive_amount' => $base * $multiplier,
        ];
    }

    public function calculateIncentive(User $engineer, Carbon $month, float $achievement, ?float $base = null): Incentive
    {
        $preview = $this->incentivePreview($achievement, $base);

        return Incentive::updateOrCreate(
            ['sales_engineer_id' => $engineer->id, 'period_month' => $month->copy()->startOfMonth()->toDateString()],
            [
                'achievement_percentage' => $achievement,
                'slab' => $preview['slab'],
                'base_incentive_amount' => $preview['base_incentive_amount'],
                'multiplier' => $preview['multiplier'],
                'final_incentive_amount' => $preview['final_incentive_amount'],
            ]
        );
    }

    public function ownerReview(?int $engineerId, Carbon $start, Carbon $end): array
    {
        $engineers = User::where('role', 'sales_engineer')
            ->when($engineerId, fn ($query) => $query->where('id', $engineerId))
            ->orderBy('name')
            ->get();

        $rows = $engineers->map(fn (User $engineer) => $this->engineerReview($engineer, $start, $end));

        return [
            'rows' => $rows,
            'totals' => $this->reviewTotals($rows),
        ];
    }

    private function engineerReview(User $engineer, Carbon $start, Carbon $end): array
    {
        $targetRow = KpiTarget::where('sales_engineer_id', $engineer->id)
            ->where('kpi_code', 'order_booking')
            ->where('period_type', 'monthly')
            ->where('valid_from', '<=', $start)
            ->where(fn ($query) => $query->whereNull('valid_to')->orWhere('valid_to', '>=', $end))
            ->latest('valid_from')
            ->first();

        $monthlyTarget = (float) ($targetRow?->target_value ?? 0);
        $weight = (float) ($targetRow?->weight_percentage ?? 100);
        $booked = (float) Rfq::where('sales_engineer_id', $engineer->id)
            ->where('current_status', 'won')
            ->whereBetween('rfq_received_date', [$start, $end])
            ->sum('total_awarded_price');
        $achievement = $monthlyTarget > 0 ? round(($booked / $monthlyTarget) * 100, 1) : 0;
        $kpiScore = round(min($achievement, 100) * $weight / 100, 2);
        $daily = DailyActivityLog::where('sales_engineer_id', $engineer->id)->whereBetween('activity_date', [$start, $end]);
        $incentive = $this->incentivePreview($achievement);

        return [
            'engineer_id' => $engineer->id,
            'engineer_name' => $engineer->name,
            'engineer_photo' => $engineer->profile_photo_url,
            'monthly_target' => $monthlyTarget,
            'order_booking' => $booked,
            'achievement' => $achievement,
            'customer_calls' => (int) (clone $daily)->sum('customer_calls'),
            'follow_up_calls' => (int) (clone $daily)->sum('follow_up_calls'),
            'customer_visits' => (int) (clone $daily)->sum('customer_visits'),
            'online_meetings' => (int) (clone $daily)->sum('online_meetings'),
            'rfqs_received' => Rfq::where('sales_engineer_id', $engineer->id)->whereBetween('rfq_received_date', [$start, $end])->count(),
            'quotations_submitted' => Quotation::whereHas('rfq', fn ($query) => $query->where('sales_engineer_id', $engineer->id))
                ->where(function ($query) use ($start, $end) {
                    $query->whereBetween('actual_submitted_date', [$start, $end])
                        ->orWhere(function ($inner) use ($start, $end) {
                            $inner->whereNull('actual_submitted_date')
                                ->whereIn('status', ['submitted', 'under_review', 'won', 'lost'])
                                ->whereBetween('quoted_date', [$start, $end]);
                        });
                })
                ->count(),
            'kpi_score' => $kpiScore,
            'incentive' => $incentive,
        ];
    }

    private function reviewTotals(Collection $rows): array
    {
        $monthlyTarget = (float) $rows->sum('monthly_target');
        $booked = (float) $rows->sum('order_booking');
        $achievement = $monthlyTarget > 0 ? round(($booked / $monthlyTarget) * 100, 1) : 0;
        $incentiveTotal = (float) $rows->sum(fn ($row) => $row['incentive']['final_incentive_amount']);

        return [
            'monthly_target' => $monthlyTarget,
            'order_booking' => $booked,
            'achievement' => $achievement,
            'customer_calls' => (int) $rows->sum('customer_calls'),
            'follow_up_calls' => (int) $rows->sum('follow_up_calls'),
            'customer_visits' => (int) $rows->sum('customer_visits'),
            'online_meetings' => (int) $rows->sum('online_meetings'),
            'rfqs_received' => (int) $rows->sum('rfqs_received'),
            'quotations_submitted' => (int) $rows->sum('quotations_submitted'),
            'kpi_score' => $rows->count() ? round($rows->avg('kpi_score'), 2) : 0,
            'incentive' => array_merge($this->incentivePreview($achievement), [
                'final_incentive_amount' => $incentiveTotal,
                'slab_label' => $rows->count() > 1 ? 'Sum of engineer incentives' : $this->incentivePreview($achievement)['slab_label'],
            ]),
        ];
    }
}
