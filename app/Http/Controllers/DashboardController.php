<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\DebtReceivable;
use App\Models\FinanceTransaction;
use App\Models\Material;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = today();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        $selectedYear = (int) $request->query('year', $today->year);
        $availableYears = collect([$today->year, $selectedYear])
            ->merge(Order::query()->pluck('order_date')->map(fn ($date) => $date ? Carbon::parse($date)->year : null))
            ->merge(FinanceTransaction::query()->pluck('transaction_date')->map(fn ($date) => $date ? Carbon::parse($date)->year : null))
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();

        $approvedIncome = FinanceTransaction::query()
            ->where('transaction_type', 'income')
            ->where('status', 'disetujui');

        $approvedExpense = FinanceTransaction::query()
            ->where('transaction_type', 'expense')
            ->where('status', 'disetujui');

        $lowStockMaterials = Material::query()
            ->with(['category', 'stock'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('min_stock', '<=', 0)
                    ->orWhereDoesntHave('stock')
                    ->orWhereHas('stock', fn ($stock) => $stock
                        ->where('qty', '<=', 0)
                        ->orWhereColumn('qty', '<=', 'materials.min_stock'));
            })
            ->orderBy('name')
            ->limit(8)
            ->get();

        $months = collect(range(1, 12))->map(fn (int $month) => Carbon::create($selectedYear, $month, 1));

        $revenueChart = [
            'labels' => $months->map(fn (Carbon $date) => $date->translatedFormat('M'))->values(),
            'income' => $months->map(fn (Carbon $date) => (float) FinanceTransaction::query()
                ->where('transaction_type', 'income')
                ->where('status', 'disetujui')
                ->whereBetween('transaction_date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                ->sum('amount'))->values(),
            'expense' => $months->map(fn (Carbon $date) => (float) FinanceTransaction::query()
                ->where('transaction_type', 'expense')
                ->where('status', 'disetujui')
                ->whereBetween('transaction_date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                ->sum('amount'))->values(),
        ];

        $orderChart = [
            'labels' => $months->map(fn (Carbon $date) => $date->translatedFormat('M'))->values(),
            'open' => $months->map(fn (Carbon $date) => Order::query()
                ->where('status', 'open')
                ->whereBetween('order_date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                ->count())->values(),
            'done' => $months->map(fn (Carbon $date) => Order::query()
                ->where('status', 'selesai')
                ->whereBetween('order_date', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                ->count())->values(),
        ];

        $investmentIncome = FinanceTransaction::query()
            ->where('transaction_type', 'income')
            ->where('status', 'disetujui')
            ->where(function ($query) {
                $query->whereHas('item.category', fn ($category) => $category
                    ->where('name', 'like', '%investasi%')
                    ->orWhere('code', 'like', '%investasi%'))
                    ->orWhereHas('item', fn ($item) => $item
                        ->where('name', 'like', '%investasi%')
                        ->orWhere('code', 'like', '%investasi%'))
                    ->orWhere('activity', 'like', '%investasi%');
            });

        $expenseCategories = FinanceTransaction::query()
            ->selectRaw("COALESCE(finance_categories.name, finance_items.name, 'Lainnya') as category_name, SUM(finance_transactions.amount) as total_amount")
            ->leftJoin('finance_items', 'finance_transactions.finance_item_id', '=', 'finance_items.id')
            ->leftJoin('finance_categories', 'finance_items.finance_category_id', '=', 'finance_categories.id')
            ->where('finance_transactions.transaction_type', 'expense')
            ->where('finance_transactions.status', 'disetujui')
            ->groupByRaw("COALESCE(finance_categories.name, finance_items.name, 'Lainnya')")
            ->orderByDesc('total_amount')
            ->limit(6)
            ->get();

        $expenseCategoryTotal = (float) $expenseCategories->sum('total_amount');

        $debtReceivableStats = [
            'total_debt' => DebtReceivable::query()->where('type', 'debt')->where('status', 'disetujui')->sum('amount'),
            'remaining_debt' => DebtReceivable::query()->where('type', 'debt')->where('status', 'disetujui')->sum('remaining_amount'),
            'total_receivable' => DebtReceivable::query()->where('type', 'receivable')->where('status', 'disetujui')->sum('amount'),
            'remaining_receivable' => DebtReceivable::query()->where('type', 'receivable')->where('status', 'disetujui')->sum('remaining_amount'),
            'overdue' => DebtReceivable::query()
                ->where('status', 'disetujui')
                ->whereDate('due_date', '<', $today)
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount'),
            'deadline' => DebtReceivable::query()
                ->where('status', 'disetujui')
                ->whereBetween('due_date', [$today, $today->copy()->addDays(7)])
                ->where('remaining_amount', '>', 0)
                ->sum('remaining_amount'),
        ];

        $bankAccounts = BankAccount::query()
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->orderBy('account_name')
            ->get();

        $totalIncome = (clone $approvedIncome)->sum('amount');
        $totalExpense = (clone $approvedExpense)->sum('amount');

        $stats = [
            'orders_today' => Order::query()->whereDate('order_date', $today)->count(),
            'orders_month' => Order::query()->whereBetween('order_date', [$monthStart, $monthEnd])->count(),
            'orders_total' => Order::query()->count(),
            'revenue_daily' => (clone $approvedIncome)->whereDate('transaction_date', $today)->sum('amount'),
            'revenue_monthly' => (clone $approvedIncome)->whereBetween('transaction_date', [$monthStart, $monthEnd])->sum('amount'),
            'revenue_total' => $totalIncome,
            'investment_total' => (clone $investmentIncome)->sum('amount'),
            'money_out' => $totalExpense,
            'net_revenue' => $totalIncome - $totalExpense,
            'materials_low' => Material::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->where('min_stock', '<=', 0)
                        ->orWhereDoesntHave('stock')
                        ->orWhereHas('stock', fn ($stock) => $stock
                            ->where('qty', '<=', 0)
                            ->orWhereColumn('qty', '<=', 'materials.min_stock'));
                })
                ->count(),
            'bank_balance_total' => $bankAccounts->sum(fn (BankAccount $account) => (float) $account->balance),
        ];

        return view('dashboard.index', compact(
            'availableYears',
            'selectedYear',
            'stats',
            'revenueChart',
            'orderChart',
            'lowStockMaterials',
            'debtReceivableStats',
            'expenseCategories',
            'expenseCategoryTotal',
            'bankAccounts'
        ));
    }
}
