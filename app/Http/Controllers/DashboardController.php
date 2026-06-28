<?php

namespace App\Http\Controllers;

use App\Http\Resources\AccountResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\TopExpenseCategoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request){
        $user = $request->user();
        $totalIncome = $user->transactions()->where('type', 'income')->sum('amount');
        $totalExpense = $user->transactions()->where('type', 'expense')->sum('amount');
        $initialBalance = $user->accounts()->sum('initial_balance');
        $totalBalance = $initialBalance + $totalIncome - $totalExpense;
        $incomeThisMonth = $user->transactions()->where('type', 'income')->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->sum('amount');
        $expenseThisMonth = $user->transactions()->where('type', 'expense')->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->sum('amount');
        $transactionsToday = $user->transactions()->whereDate('transaction_date', today())->count();
        $transactionsThisMonth = $user->transactions()->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->count();
        $topExpenseCategories = $user->transactions()
            ->select(
                'category_id',
                DB::raw('SUM(amount) as total')
            )
            ->where('type', 'expense')
            ->groupBy('category_id')
            ->with('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $item->total = (int)$item->total;
                return $item;
            });
        $accountBalances = $user->accounts()
            ->withSum([
                'transactions as income' => function ($q){
                    $q->where('type', 'income');
                }
            ], 'amount')
            ->withSum([
                'transactions as expense' => function ($q){
                    $q->where('type', 'expense');
                }
            ], 'amount')
            ->get()
            ->map(function ($account){
                $account->total_income = (int)$account->income ?? 0;
                $account->total_expense = (int)$account->expense ?? 0;
                $account->total_balance = $account->initial_balance + ($account->income ?? 0) - ($account->expense ?? 0);
                return new AccountResource($account);
            });
        return $this->sendResponse([
            'user' => $user,
            'total_income' => (int)$totalIncome,
            'total_expense' => (int)$totalExpense,
            'initial_balance' => (int)$initialBalance,
            'total_balance' => (int)$totalBalance,
            'income_this_month' => (int)$incomeThisMonth,
            'expense_this_month' => (int)$expenseThisMonth,
            'transactions_today' => (int)$transactionsToday,
            'transactions_this_month' => (int)$transactionsThisMonth,
            'top_expense_categories' => TopExpenseCategoryResource::collection($topExpenseCategories),
            'account_balances' => $accountBalances,
        ], 'Dashboard data retrieved successfully');
    }
}
