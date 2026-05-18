<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // ─── CREATE ──────────────────────────────────────────────────────────────

    public function CreateCustomer(Request $request)
    {
        try {
            $user_id = $request->header('id');

            $request->validate([
                'name'    => 'required|string|max:100',
                'email'   => 'required|email|max:100',
                'mobile'  => 'required|string|max:30',
                'address' => 'required|string|max:500',
            ]);

            Customer::create([
                'name'             => $request->name,
                'email'            => $request->email,
                'mobile'           => $request->mobile,
                'address'          => $request->address,
                'contact_person'   => $request->contact_person,
                'tax_id'           => $request->tax_id,
                'shipping_address' => $request->shipping_address,
                'user_id'          => $user_id,
            ]);

            return redirect('/CustomerPage')->with([
                'message' => 'Customer created successfully',
                'status'  => true,
                'error'   => '',
            ]);
        } catch (\Exception $e) {
            return redirect('/CustomerPage')->with([
                'message' => 'Customer could not be created. Please try again.',
                'status'  => false,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    // ─── LIST (JSON) ─────────────────────────────────────────────────────────

    public function CustomerList(Request $request)
    {
        try {
            $user_id    = $request->header('id');
            $allCustomer = Customer::where('user_id', $user_id)->get();
            return response()->json([
                'status'  => 'Success',
                'message' => 'All Customers found',
                'data'    => $allCustomer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'Failed',
                'message' => 'Customers not found',
            ]);
        }
    }

    // ─── DETAIL ──────────────────────────────────────────────────────────────

    public function CustomerDetail(Request $request)
    {
        try {
            $user_id        = $request->header('id');
            $customerDetail = Customer::where('id', $request->input('id'))
                ->where('user_id', $user_id)
                ->firstOrFail();
            return response()->json([
                'status'  => 'Success',
                'message' => 'Customer Details found',
                'data'    => $customerDetail,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'Failed',
                'message' => 'Customer not found',
            ]);
        }
    }

    // ─── UPDATE ──────────────────────────────────────────────────────────────

    public function CustomerUpdate(Request $request)
    {
        try {
            $user_id = $request->header('id');

            $request->validate([
                'name'    => 'required|string|max:100',
                'email'   => 'required|email|max:100',
                'mobile'  => 'required|string|max:30',
                'address' => 'required|string|max:500',
            ]);

            Customer::where('id', $request->input('id'))
                ->where('user_id', $user_id)
                ->update([
                    'name'             => $request->input('name'),
                    'email'            => $request->input('email'),
                    'mobile'           => $request->input('mobile'),
                    'address'          => $request->input('address'),
                    'contact_person'   => $request->input('contact_person'),
                    'tax_id'           => $request->input('tax_id'),
                    'shipping_address' => $request->input('shipping_address'),
                ]);

            return redirect('/CustomerPage')->with([
                'status'  => true,
                'message' => 'Customer updated successfully',
                'error'   => '',
            ]);
        } catch (\Exception $e) {
            return redirect('/CustomerPage')->with([
                'status'  => false,
                'message' => 'Customer could not be updated. Please try again.',
                'error'   => '',
            ]);
        }
    }

    // ─── DELETE ──────────────────────────────────────────────────────────────

    public function CustomerDelete(Request $request, $id)
    {
        try {
            $user_id = $request->header('id');
            Customer::where('id', $id)->where('user_id', $user_id)->delete();

            return redirect()->back()->with([
                'status'  => true,
                'message' => 'Customer deleted successfully',
                'error'   => '',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with([
                'status'  => false,
                'message' => 'Customer could not be deleted. Please try again.',
                'error'   => '',
            ]);
        }
    }

    // ─── PAGES ───────────────────────────────────────────────────────────────

    public function CustomerPage(Request $request)
    {
        $user_id     = (int) $request->header('id');
        $now         = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfYear  = $now->copy()->startOfYear();

        $customers = Customer::where('user_id', $user_id)->latest()->get();

        // Batch-load all invoices for these customers in ONE query (avoids N+1)
        $customerIds = $customers->pluck('id')->toArray();

        $allInvoices = Invoice::where('user_id', $user_id)
            ->whereIn('customer_id', $customerIds)
            ->get(['id', 'customer_id', 'total', 'payable', 'created_at']);

        $invoicesByCustomer = $allInvoices->groupBy('customer_id');

        $customersWithStats = $customers->map(function ($customer) use (
            $invoicesByCustomer,
            $startOfMonth,
            $startOfYear
        ) {
            $invoices = $invoicesByCustomer->get($customer->id, collect());

            $totalBusiness   = $invoices->sum(fn($i) => (float) $i->payable);
            $outstanding     = $invoices->sum(fn($i) => max(0, (float) $i->total - (float) $i->payable));
            $monthlyBusiness = $invoices
                ->filter(fn($i) => $i->created_at >= $startOfMonth)
                ->sum(fn($i) => (float) $i->payable);
            $yearlyBusiness  = $invoices
                ->filter(fn($i) => $i->created_at >= $startOfYear)
                ->sum(fn($i) => (float) $i->payable);

            return array_merge($customer->toArray(), [
                'stats' => [
                    'invoiceCount'   => $invoices->count(),
                    'outstanding'    => round($outstanding, 2),
                    'totalBusiness'  => round($totalBusiness, 2),
                    'monthlyBusiness'=> round($monthlyBusiness, 2),
                    'yearlyBusiness' => round($yearlyBusiness, 2),
                ],
            ]);
        });

        return Inertia::render('CustomerPage', ['customers' => $customersWithStats]);
    }

    public function CustomerSavePage(Request $request)
    {
        $user_id  = $request->header('id');
        $id       = $request->query('id');
        $customer = Customer::where('id', $id)->where('user_id', $user_id)->first();
        return Inertia::render('CustomerSavePage', ['customer' => $customer]);
    }

    // ─── REPORT (per-customer invoices) ──────────────────────────────────────

    /**
     * GET /customer-invoice-report?customer_id=X&type=monthly|yearly
     * Returns filtered invoices for the report modal.
     */
    public function CustomerInvoiceReport(Request $request)
    {
        $user_id     = (int) $request->header('id');
        $customer_id = (int) $request->query('customer_id');
        $type        = $request->query('type', 'monthly'); // monthly | yearly

        $now   = now();
        $query = Invoice::where('user_id', $user_id)
            ->where('customer_id', $customer_id)
            ->select(['id', 'poNumber', 'prNumber', 'total', 'payable', 'created_at']);

        if ($type === 'monthly') {
            $query->whereYear('created_at', $now->year)
                  ->whereMonth('created_at', $now->month);
        } else {
            $query->whereYear('created_at', $now->year);
        }

        $invoices = $query->latest()->get()->map(fn($inv) => [
            'id'       => $inv->id,
            'invoiceNo'=> $inv->poNumber ?: 'INV-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT),
            'prNumber' => $inv->prNumber,
            'total'    => (float) $inv->total,
            'payable'  => (float) $inv->payable,
            'date'     => $inv->created_at->format('d M Y'),
        ]);

        return response()->json([
            'status'  => 'Success',
            'invoices'=> $invoices,
            'total'   => $invoices->sum('payable'),
        ]);
    }
}
