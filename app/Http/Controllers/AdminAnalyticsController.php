<?php

namespace App\Http\Controllers;

use App\Services\DashboardAnalyticsService;

class AdminAnalyticsController extends Controller
{
    protected $analytics;

    public function __construct(DashboardAnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    public function index()
    {
        $summary = $this->analytics->getGeneralSummary();
        $salesPerDay = $this->analytics->getSalesPerDay();
        $salesPerMonth = $this->analytics->getSalesPerMonth();
        $topProducts = $this->analytics->getTopProducts();
        $topCategories = $this->analytics->getTopCategories();
        $paymentMethods = $this->analytics->getPaymentMethods();
        $alerts = $this->analytics->getAlerts();
        $recentActivity = $this->analytics->getRecentActivity();

        return view('admin.analytics', compact(
            'summary',
            'salesPerDay',
            'salesPerMonth',
            'topProducts',
            'topCategories',
            'paymentMethods',
            'alerts',
            'recentActivity'
        ));
    }
}
