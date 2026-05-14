<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtInstallment;
use App\Models\DebtPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HutangController extends Controller
{
    // ---------------------------------------------------------------
    // INDEX — dashboard ringkasan + senarai hutang
    // ---------------------------------------------------------------
    public function index(Request $request)
    {
        $query = Auth::user()->debts()->with(['payments', 'installments']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('search')) {
            $query->where('contact_name', 'like', '%' . $request->search . '%');
        }

        $debts = $query->latest()->get();

        $totalIOwe    = Auth::user()->debts()->iOwe()->active()->sum('total_amount')
                      - Auth::user()->debts()->iOwe()->active()->sum('paid_amount');
        $totalTheyOwe = Auth::user()->debts()->theyOwe()->active()->sum('total_amount')
                      - Auth::user()->debts()->theyOwe()->active()->sum('paid_amount');

        return view('hutang.index', compact('debts', 'totalIOwe', 'totalTheyOwe'));
    }

    // ---------------------------------------------------------------
    // CREATE / STORE
    // ---------------------------------------------------------------
    public function create()
    {
        return view('hutang.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'contact_name'           => 'required|string|max:255',
            'direction'              => 'required|in:i_owe,they_owe',
            'total_amount'           => 'required|numeric|min:0.01',
            'payment_method'         => 'required|string|max:50',
            'description'            => 'nullable|string',
            'due_day_of_month'       => 'nullable|integer|min:1|max:31',
            'warning_days'           => 'nullable|array',
            'warning_days.*'         => 'integer|in:1,3,7,14',
            'warn_on_due_date'       => 'boolean',
            'warn_if_overdue'        => 'boolean',
            'is_installment'         => 'boolean',
            'installment_count'      => 'required_if:is_installment,true|nullable|integer|min:1|max:360',
            'installment_frequency'  => 'nullable|in:monthly,weekly,custom',
            'first_installment_date' => 'required_if:is_installment,true|nullable|date',
        ]);

        $validated['user_id']          = Auth::id();
        $validated['paid_amount']      = 0;
        $validated['status']           = 'pending';
        $validated['warn_on_due_date'] = $request->boolean('warn_on_due_date', true);
        $validated['warn_if_overdue']  = $request->boolean('warn_if_overdue', true);
        $validated['is_installment']   = $request->boolean('is_installment');

        $debt = Debt::create($validated);

        // Generate installment schedule if selected
        if ($debt->is_installment && $debt->installment_count && $debt->first_installment_date) {
            $schedule = Debt::buildInstallmentSchedule(
                (float) $debt->total_amount,
                $debt->installment_count,
                $debt->installment_frequency ?? 'monthly',
                Carbon::parse($debt->first_installment_date)
            );

            foreach ($schedule as $item) {
                DebtInstallment::create([
                    'debt_id'             => $debt->id,
                    'installment_number'  => $item['number'],
                    'amount'              => $item['amount'],
                    'due_date'            => $item['due_date'],
                    'status'              => 'pending',
                ]);
            }
        }

        return redirect()->route('hutang.show', $debt)->with('success', 'Rekod hutang berjaya ditambah.');
    }

    // ---------------------------------------------------------------
    // SHOW — detail hutang + senarai ansuran + bayaran
    // ---------------------------------------------------------------
    public function show(Debt $hutang)
    {
        $this->authorizeDebt($hutang);

        $hutang->load(['payments', 'installments']);

        return view('hutang.show', ['debt' => $hutang]);
    }

    // ---------------------------------------------------------------
    // EDIT / UPDATE
    // ---------------------------------------------------------------
    public function edit(Debt $hutang)
    {
        $this->authorizeDebt($hutang);

        return view('hutang.edit', ['debt' => $hutang]);
    }

    public function update(Request $request, Debt $hutang)
    {
        $this->authorizeDebt($hutang);

        $validated = $request->validate([
            'contact_name'     => 'required|string|max:255',
            'direction'        => 'required|in:i_owe,they_owe',
            'total_amount'     => 'required|numeric|min:0.01',
            'payment_method'   => 'required|string|max:50',
            'description'      => 'nullable|string',
            'due_day_of_month' => 'nullable|integer|min:1|max:31',
            'warning_days'     => 'nullable|array',
            'warning_days.*'   => 'integer|in:1,3,7,14',
            'warn_on_due_date' => 'boolean',
            'warn_if_overdue'  => 'boolean',
        ]);

        $validated['warn_on_due_date'] = $request->boolean('warn_on_due_date', true);
        $validated['warn_if_overdue']  = $request->boolean('warn_if_overdue', true);

        $hutang->update($validated);
        $hutang->recalculateStatus();
        $hutang->save();

        return redirect()->route('hutang.show', $hutang)->with('success', 'Rekod hutang berjaya dikemaskini.');
    }

    // ---------------------------------------------------------------
    // DESTROY
    // ---------------------------------------------------------------
    public function destroy(Debt $hutang)
    {
        $this->authorizeDebt($hutang);
        $hutang->delete();

        return redirect()->route('hutang.index')->with('success', 'Rekod hutang dipadam.');
    }

    // ---------------------------------------------------------------
    // LOG BAYARAN (store payment for a debt)
    // ---------------------------------------------------------------
    public function storePayment(Request $request, Debt $hutang)
    {
        $this->authorizeDebt($hutang);

        $validated = $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_date'   => 'required|date',
            'payment_method' => 'required|string|max:50',
            'notes'          => 'nullable|string',
            'proof'          => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('proofs', 'public');
        }

        DebtPayment::create([
            'debt_id'        => $hutang->id,
            'amount'         => $validated['amount'],
            'payment_date'   => $validated['payment_date'],
            'payment_method' => $validated['payment_method'],
            'notes'          => $validated['notes'] ?? null,
            'proof_path'     => $proofPath,
            'proof_source'   => 'web',
        ]);

        // Update paid_amount and status
        $hutang->paid_amount = $hutang->payments()->sum('amount');
        $hutang->recalculateStatus();
        $hutang->save();

        return redirect()->route('hutang.show', $hutang)->with('success', 'Bayaran berjaya direkodkan.');
    }

    // ---------------------------------------------------------------
    // MARK INSTALLMENT AS PAID
    // ---------------------------------------------------------------
    public function markInstallmentPaid(Request $request, Debt $hutang, DebtInstallment $installment)
    {
        $this->authorizeDebt($hutang);

        if ($installment->debt_id !== $hutang->id) {
            abort(404);
        }

        $request->validate([
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes' => 'nullable|string',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('proofs', 'public');
        }

        $installment->update([
            'status'    => 'paid',
            'paid_at'   => now(),
            'proof_path'=> $proofPath ?? $installment->proof_path,
            'proof_source' => $proofPath ? 'web' : $installment->proof_source,
            'notes'     => $request->input('notes', $installment->notes),
        ]);

        // Create matching payment record
        DebtPayment::create([
            'debt_id'        => $hutang->id,
            'amount'         => $installment->amount,
            'payment_date'   => now()->toDateString(),
            'payment_method' => $hutang->payment_method,
            'notes'          => "Ansuran {$installment->installment_number}",
            'proof_path'     => $proofPath,
            'proof_source'   => 'web',
        ]);

        $hutang->paid_amount = $hutang->payments()->sum('amount');
        $hutang->recalculateStatus();
        $hutang->save();

        return redirect()->route('hutang.show', $hutang)->with('success', "Ansuran {$installment->installment_number} ditandakan bayar.");
    }

    // ---------------------------------------------------------------
    // Private helper
    // ---------------------------------------------------------------
    private function authorizeDebt(Debt $debt): void
    {
        if ($debt->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
