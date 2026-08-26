<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Rfq;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $term = trim((string) $request->input('q'));
        abort_if($term === '', 422, 'Enter a search term.');
        $user = $request->user();
        $customers = Customer::where(function ($query) use ($term): void {
            $query->where('company_name', 'like', "%{$term}%")->orWhere('customer_code', 'like', "%{$term}%");
        })->when($user->isSalesEngineer(), fn ($query) => $query->where('assigned_sales_engineer_id', $user->id))->take(10)->get();
        $rfqs = Rfq::with('customer')->where(function ($query) use ($term): void {
            $query->where('rfq_number', 'like', "%{$term}%")->orWhere('rfq_description', 'like', "%{$term}%");
        })->when($user->isSalesEngineer(), fn ($query) => $query->where('sales_engineer_id', $user->id))->take(10)->get();
        return view('content.search', compact('term', 'customers', 'rfqs'));
    }
}
