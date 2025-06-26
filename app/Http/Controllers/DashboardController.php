<?php

namespace App\Http\Controllers;

use App\Models\PointLog;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();
        $user_query = User::query();

        $last_month_users = (clone $user_query)
            ->whereDate('created_at', '<=', $endOfLastMonth)
            ->count();

        $total_users = (clone $user_query)
            ->count();

        $users_trend = $total_users - $last_month_users;

        $product_query = Product::query();

        $last_month_product = (clone $product_query)
            ->whereDate('created_at', '<=', $endOfLastMonth)
            ->count();

        $total_products = (clone $product_query)
            ->count();

        $products_trend = $total_products - $last_month_product;

        $point_query = PointLog::query();

        $last_month_product = (clone $point_query)
            ->where('earning_point', '>', 0)
            ->whereDate('created_at', '<=', $endOfLastMonth)
            ->sum('earning_point');

        $points_given = (clone $point_query)
            ->where('earning_point', '>', 0)
            ->sum('earning_point');

        $points_given_trend = $points_given - $last_month_product;

        return Inertia::render('Dashboard/Dashboard', [
            'totalUsers' => (float) $total_users,
            'usersTrend' => (float) $users_trend,
            'totalProducts' => (float) $total_products,
            'productsTrend' => (float) $products_trend,
            'pointsGiven' => (float) $points_given,
            'pointsGivenTrend' => (float) $points_given_trend,
        ]);
    }

    public function get_member_analysis_by_year(Request $request)
    {
        $year = (int) $request->input('year', date('Y'));
        $currentDate = Carbon::now();

        // Query total users joined per month in the given year
        $query = User::query()
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(id) as total_users')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Generate Labels
        $labels = collect(range(1, 12))->map(function ($month) {
            return Carbon::createFromDate(null, $month, 1)->format('F');
        })->toArray();

        // Build Chart Data
        $chartData = [
            'labels' => array_map(fn($label) => trans("public.$label"), $labels),
            'datasets' => [
                [
                    'label' => trans('public.registered_member'),
                    'data' => array_map(function ($month) use ($query) {
                        return $query->firstWhere('month', $month)?->total_users ?? 0;
                    }, range(1, 12)),
                    'backgroundColor' => '#f43f5e',
                    'borderRadius' => 12,
                    'fill' => true,
                ],
            ],
        ];

        return response()->json([
            'chartData' => $chartData,
        ]);
    }
}
