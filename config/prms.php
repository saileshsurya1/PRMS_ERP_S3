<?php

return [
    'departments' => [
        'Marketing & Sales', 'Design', 'Project', 'Purchase', 'Manufacturing / Production',
        'Assembly', 'Quality (Inward, In-process, PDI & CMM)', 'HR', 'Store', 'ERP', 'Maintenance', 'IT',
    ],
    'rfq_statuses' => ['follow_up', 'follow_through', 'quoted', 'order_awarded', 'invoiced', 'order_cancelled'],
    'kpi_targets' => [
        'monthly_order_booking' => 10000000, 'monthly_rfqs' => 20, 'monthly_quotations' => 20,
        'monthly_conversion_rate' => 30, 'monthly_customer_meetings' => 32, 'monthly_new_customers' => 3,
        'monthly_repeat_orders' => 40, 'monthly_average_quote_hours' => 48, 'monthly_collection_efficiency' => 95,
        'daily_customer_calls' => 15, 'daily_follow_up_calls' => 20, 'daily_customer_visits' => 2,
        'daily_online_meetings' => 2, 'weekly_customer_visits' => 8, 'weekly_new_customers_contacted' => 5,
        'weekly_rfqs' => 5, 'weekly_orders_closed' => 2500000,
    ],
    'incentive_slabs' => [
        ['maximum' => 80, 'multiplier' => 0, 'code' => 'no_incentive'],
        ['maximum' => 100, 'multiplier' => 1, 'code' => 'standard'],
        ['maximum' => 110, 'multiplier' => 1.5, 'code' => 'one_point_five_x'],
        ['maximum' => null, 'multiplier' => 2, 'code' => 'two_x_recognition'],
    ],
    'base_currency' => env('PRMS_BASE_CURRENCY', 'INR'),
    'inr_rate' => (float) env('PRMS_INR_RATE', 1),
    'workday_hours_min' => (float) env('PRMS_WORKDAY_HOURS_MIN', 8),
    'workday_hours_max' => (float) env('PRMS_WORKDAY_HOURS_MAX', 9),
    'standard_incentive_amount' => (float) env('PRMS_STANDARD_INCENTIVE', 0),
];