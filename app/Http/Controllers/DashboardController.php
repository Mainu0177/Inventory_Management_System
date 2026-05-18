<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Build and return dashboard summary data.
     */
    private function buildSummary(int $user_id): array
    {
        // -- Stat Cards --
        $totalRevenue    = Invoice::where('user_id', $user_id)->sum('payable');
        $totalBilled     = Invoice::where('user_id', $user_id)->sum('total');
        $outstanding     = max(0, $totalBilled - $totalRevenue);
        $activeClients   = Customer::where('user_id', $user_id)->count();
        $productCount    = Product::where('user_id', $user_id)->count();
        $invoiceCount    = Invoice::where('user_id', $user_id)->count();

        $inventoryValue  = Product::where('user_id', $user_id)
            ->selectRaw('COALESCE(SUM(price * unit), 0) as value')
            ->value('value') ?? 0;

        // -- Monthly Revenue (last 6 months) for chart --
        $monthlyRevenue = Invoice::where('user_id', $user_id)
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(payable) as amount')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at) ASC, MONTH(created_at) ASC')
            ->get()
            ->map(fn($row) => [
                'month'  => date('M Y', mktime(0, 0, 0, $row->month, 1, $row->year)),
                'amount' => (float) $row->amount,
            ])
            ->values()
            ->toArray();

        // -- Low Stock Products (unit <= low_stock_threshold) --
        $lowStockProducts = Product::where('user_id', $user_id)
            ->whereColumn('unit', '<=', 'low_stock_threshold')
            ->orderBy('unit', 'asc')
            ->get(['id', 'name', 'unit', 'price', 'low_stock_threshold'])
            ->map(fn($p) => [
                'id'        => $p->id,
                'name'      => $p->name,
                'stock'     => (int) $p->unit,
                'price'     => (float) $p->price,
                'threshold' => (int) ($p->low_stock_threshold ?? 10),
            ])
            ->values()
            ->toArray();

        return [
            'totalRevenue'    => (float) $totalRevenue,
            'outstanding'     => (float) $outstanding,
            'activeClients'   => (int)   $activeClients,
            'inventoryValue'  => (float) $inventoryValue,
            'productCount'    => (int)   $productCount,
            'invoiceCount'    => (int)   $invoiceCount,
            'monthlyRevenue'  => $monthlyRevenue,
            'lowStockProducts'=> $lowStockProducts,
        ];
    }

    /**
     * Inertia page – renders DashboardPage with full summary.
     */
    public function DashboardPage(Request $request)
    {
        $user_id = (int) $request->header('id');
        $summary = $this->buildSummary($user_id);

        return Inertia::render('DashboardPage', ['summary' => $summary]);
    }

    /**
     * JSON endpoint – used by the legacy /dashboard-summary route.
     */
    public function DashboardController(Request $request)
    {
        $user_id = (int) $request->header('id');
        return response()->json($this->buildSummary($user_id));
    }
}
