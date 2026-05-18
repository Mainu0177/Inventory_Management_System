<?php

namespace App\Http\Controllers;

use Exception;
use Inertia\Inertia;
use App\Models\Invoice;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankingController extends Controller
{
    /**
     * Render the main Banking dashboard with accounts and transaction history.
     */
    public function BankingPage(Request $request)
    {
        $user_id = $request->header('id');

        // Fetch bank accounts
        $accountsRaw = BankAccount::where('user_id', $user_id)
            ->orderBy('id', 'asc')
            ->get();

        $accounts = $accountsRaw->map(function ($acc) {
            return [
                'id' => $acc->id,
                'accountName' => $acc->account_name,
                'bankName' => $acc->bank_name,
                'accountNumber' => $acc->account_number,
                'balance' => (float)$acc->balance,
            ];
        });

        // Fetch transactions
        $transactionsRaw = BankTransaction::where('user_id', $user_id)
            ->orderBy('date', 'desc')
            ->get();

        $transactions = $transactionsRaw->map(function ($t) {
            return [
                'id' => $t->id,
                'bankAccountId' => $t->bank_account_id,
                'amount' => (float)$t->amount,
                'type' => $t->type,
                'category' => $t->category,
                'reference' => $t->reference,
                'date' => $t->date ? $t->date->toIso8601String() : $t->created_at->toIso8601String(),
                'linkedInvoiceId' => $t->linked_invoice_id,
            ];
        });

        return Inertia::render('BankingPage', [
            'accounts' => $accounts,
            'transactions' => $transactions,
        ]);
    }

    /**
     * Create a new corporate bank account.
     */
    public function AddBankAccount(Request $request)
    {
        $user_id = $request->header('id');

        $request->validate([
            'accountName' => 'required|string|max:255',
            'bankName' => 'required|string|max:255',
            'accountNumber' => 'required|string|max:100',
            'balance' => 'required|numeric|min:0',
        ]);

        try {
            BankAccount::create([
                'user_id' => $user_id,
                'account_name' => $request->input('accountName'),
                'bank_name' => $request->input('bankName'),
                'account_number' => $request->input('accountNumber'),
                'balance' => $request->input('balance'),
            ]);

            return redirect('/BankingPage')->with([
                'status' => true,
                'message' => 'Bank account added successfully',
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Failed to add bank account: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Process/log a new bank transaction and update balances under transaction locks.
     */
    public function AddTransaction(Request $request)
    {
        $user_id = $request->header('id');

        $request->validate([
            'bankAccountId' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|string|in:Deposit,Withdrawal',
            'category' => 'required|string|max:255',
            'reference' => 'nullable|string|max:1000',
            'linkedInvoiceId' => 'nullable|exists:invoices,id',
        ]);

        DB::beginTransaction();
        try {
            $accountId = $request->input('bankAccountId');
            $amount = (float)$request->input('amount');
            $type = $request->input('type');

            // Find and lock the bank account to prevent race conditions
            $account = BankAccount::where('user_id', $user_id)
                ->where('id', $accountId)
                ->lockForUpdate()
                ->firstOrFail();

            // Calculate new balance
            $currentBalance = (float)$account->balance;
            if ($type === 'Deposit') {
                $newBalance = $currentBalance + $amount;
            } else {
                if ($currentBalance < $amount) {
                    throw new Exception("Insufficient funds in account.");
                }
                $newBalance = $currentBalance - $amount;
            }

            // Update bank account
            $account->update(['balance' => $newBalance]);

            // Save transaction record
            $transaction = BankTransaction::create([
                'user_id' => $user_id,
                'bank_account_id' => $accountId,
                'amount' => $amount,
                'type' => $type,
                'category' => $request->input('category'),
                'reference' => $request->input('reference'),
                'date' => now(),
                'linked_invoice_id' => $request->input('linkedInvoiceId'),
            ]);

            // If an invoice is linked, update its payment paidAmount and status
            if ($request->input('linkedInvoiceId')) {
                $invoice = Invoice::where('user_id', $user_id)
                    ->where('id', $request->input('linkedInvoiceId'))
                    ->lockForUpdate()
                    ->first();

                if ($invoice) {
                    $currentPaid = (float)$invoice->paid_amount;
                    $total = (float)$invoice->total; // using invoice.total
                    $newPaid = $currentPaid + $amount;
                    $status = ($newPaid >= $total) ? 'Paid' : 'Partial';

                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'status' => $status
                    ]);
                }
            }

            DB::commit();
            return redirect('/BankingPage')->with([
                'status' => true,
                'message' => 'Transaction processed successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with([
                'status' => false,
                'message' => 'Transaction failed: ' . $e->getMessage(),
            ]);
        }
    }
}
