<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\Expense;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    /**
     * Display the Expenses Page.
     */
    public function ExpensesPage(Request $request)
    {
        $user_id = $request->header('id');

        $expenses = Expense::where('user_id', $user_id)
            ->orderBy('date', 'desc')
            ->get();

        $accounts = BankAccount::where('user_id', $user_id)
            ->orderBy('id', 'asc')
            ->get()->map(function ($acc) {
                return [
                    'id' => $acc->id,
                    'accountName' => $acc->account_name,
                    'bankName' => $acc->bank_name,
                ];
            });

        return Inertia::render('ExpensesPage', [
            'expenses' => $expenses,
            'accounts' => $accounts,
        ]);
    }

    /**
     * Create a new expense. If paid by Bank, deducts from account and logs transaction.
     */
    public function AddExpense(Request $request)
    {
        $user_id = $request->header('id');

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'paymentMethod' => 'required|string|in:Bank,Cash',
            'bankAccountId' => 'nullable|exists:bank_accounts,id',
        ]);

        $amount = (float)$request->input('amount');
        $paymentMethod = $request->input('paymentMethod');
        $bankAccountId = $request->input('bankAccountId');

        DB::beginTransaction();
        try {
            if ($paymentMethod === 'Bank') {
                if (!$bankAccountId) {
                    throw new Exception("Bank account is required for Bank payment method.");
                }

                // Find and lock bank account
                $account = BankAccount::where('user_id', $user_id)
                    ->where('id', $bankAccountId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($account->balance < $amount) {
                    throw new Exception("Insufficient funds in the selected bank account.");
                }

                // Deduct from bank account
                $account->decrement('balance', $amount);

                // Create a bank transaction
                BankTransaction::create([
                    'user_id' => $user_id,
                    'bank_account_id' => $bankAccountId,
                    'amount' => $amount,
                    'type' => 'Withdrawal',
                    'category' => "Expense - " . $request->input('category'),
                    'reference' => "Expense: " . ($request->input('description') ?? $request->input('category')),
                    'date' => now(),
                ]);
            }

            // Create Expense record
            Expense::create([
                'user_id' => $user_id,
                'amount' => $amount,
                'category' => $request->input('category'),
                'description' => $request->input('description'),
                'payment_method' => $paymentMethod,
                'bank_account_id' => $paymentMethod === 'Bank' ? $bankAccountId : null,
                'date' => now(),
            ]);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['status' => 'Success', 'message' => 'Expense logged successfully!']);
            }
            return redirect()->back()->with(['status' => true, 'message' => 'Expense logged successfully!']);

        } catch (Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['status' => 'Error', 'message' => $e->getMessage()], 400);
            }
            return redirect()->back()->with(['status' => false, 'message' => 'Failed to log expense: ' . $e->getMessage()]);
        }
    }
}
