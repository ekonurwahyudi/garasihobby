<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\BankBalanceAdjustment;
use App\Models\BankTransfer;
use App\Models\FinanceTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function index(): View
    {
        $data = BankAccount::orderBy('bank_name')->get();
        $banks = [
            'Cash',
            'Bank Central Asia (BCA)',
            'Bank Rakyat Indonesia (BRI)',
            'Bank Negara Indonesia (BNI)',
            'Bank Mandiri',
            'Bank Syariah Indonesia (BSI)',
            'Bank Tabungan Negara (BTN)',
            'Bank CIMB Niaga',
            'Bank Danamon',
            'Bank Permata',
            'Bank OCBC NISP',
            'Bank Panin',
            'Bank Mega',
            'Bank Maybank Indonesia',
            'Bank Muamalat Indonesia',
            'Bank BTPN',
            'Bank Jago',
            'Bank Neo Commerce',
            'Bank Sinarmas',
            'Bank Bukopin',
            'Bank BJB',
            'Bank DKI',
            'Bank Jatim',
            'Bank Jateng',
            'Bank Sumut',
            'Bank Sumsel Babel',
            'Bank Nagari',
            'Bank Aceh Syariah',
            'Bank NTB Syariah',
            'SeaBank Indonesia',
            'Allo Bank',
        ];

        return view('master.bank-accounts.index', compact('data', 'banks'));
    }

    public function show(BankAccount $bank_account): View
    {
        $movements = collect();

        FinanceTransaction::with('item.category')
            ->where('bank_account_id', $bank_account->id)
            ->where('status', 'disetujui')
            ->get()
            ->each(function ($transaction) use ($movements) {
                $activity = $transaction->activity ?: $transaction->description;
                $source = match (true) {
                    Str::startsWith($activity, 'Pembelian Material') => 'Pembelian Material',
                    Str::startsWith($activity, 'Pembayaran Order') => 'Pembayaran Order',
                    Str::startsWith($activity, 'Pembelian Aset') => 'Pembelian Aset',
                    Str::startsWith($activity, 'Pembayaran Hutang') => 'Pembayaran Hutang',
                    Str::startsWith($activity, 'Penerimaan Piutang') => 'Penerimaan Piutang',
                    default => 'Input Keuangan',
                };
                $movements->push([
                    'date' => $transaction->transaction_date,
                    'reference' => $transaction->transaction_number,
                    'source' => $source,
                    'description' => $activity,
                    'type' => $transaction->transaction_type,
                    'amount' => (float) $transaction->amount,
                    'action_url' => route('finance-transactions.show', $transaction),
                ]);
            });

        BankTransfer::with(['fromBankAccount', 'toBankAccount'])
            ->where(fn ($query) => $query->where('from_bank_account_id', $bank_account->id)->orWhere('to_bank_account_id', $bank_account->id))
            ->get()
            ->each(function ($transfer) use ($movements, $bank_account) {
                $isIncome = $transfer->to_bank_account_id === $bank_account->id;
                $other = $isIncome ? $transfer->fromBankAccount : $transfer->toBankAccount;
                $movements->push([
                    'date' => $transfer->transfer_date,
                    'reference' => 'TRF-' . str_pad($transfer->id, 6, '0', STR_PAD_LEFT),
                    'source' => 'Transfer Saldo',
                    'description' => ($isIncome ? 'Transfer dari ' : 'Transfer ke ') . ($other?->bank_name ?? '-'),
                    'type' => $isIncome ? 'income' : 'expense',
                    'amount' => (float) $transfer->amount,
                    'action_url' => null,
                ]);
            });

        $adjustments = BankBalanceAdjustment::with('creator')
            ->where('bank_account_id', $bank_account->id)
            ->latest()
            ->get();

        $runningBalance = (float) $bank_account->opening_balance;
        $movements = $movements->sortBy('date')->values()->map(function ($movement) use (&$runningBalance) {
            $runningBalance += $movement['type'] === 'income' ? $movement['amount'] : -$movement['amount'];
            $movement['balance'] = $runningBalance;
            return $movement;
        })->sortByDesc('date')->values();

        return view('master.bank-accounts.show', compact('bank_account', 'movements', 'adjustments'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);
        $data['opening_balance'] = $data['balance'];
        BankAccount::create($data);
        return response()->json(['success' => true]);
    }

    public function edit(BankAccount $bank_account): JsonResponse
    {
        return response()->json($bank_account);
    }

    public function update(Request $request, BankAccount $bank_account): JsonResponse
    {
        $data = $this->validatedData($request, $bank_account);
        $oldBalance = (float) $bank_account->balance;
        $newBalance = (float) $data['balance'];

        DB::transaction(function () use ($request, $bank_account, $data, $oldBalance, $newBalance) {
            $bank_account->update($data);

            if (abs($newBalance - $oldBalance) > 0.009) {
                BankBalanceAdjustment::create([
                    'bank_account_id' => $bank_account->id,
                    'previous_balance' => $oldBalance,
                    'new_balance' => $newBalance,
                    'difference' => abs($newBalance - $oldBalance),
                    'type' => $newBalance > $oldBalance ? 'increase' : 'decrease',
                    'description' => $request->input('balance_description') ?: 'Penyesuaian saldo manual',
                    'created_by' => auth()->id(),
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function transfer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_bank_account_id' => 'required|exists:bank_accounts,id',
            'to_bank_account_id' => 'required|different:from_bank_account_id|exists:bank_accounts,id',
            'transfer_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $from = BankAccount::lockForUpdate()->findOrFail($data['from_bank_account_id']);
            $to = BankAccount::lockForUpdate()->findOrFail($data['to_bank_account_id']);

            if ((float) $from->balance < (float) $data['amount']) {
                throw ValidationException::withMessages(['amount' => 'Saldo rekening asal tidak mencukupi.']);
            }

            $from->decrement('balance', $data['amount']);
            $to->increment('balance', $data['amount']);
            BankTransfer::create($data + ['created_by' => auth()->id()]);
        });

        return back()->with('success', 'Transfer saldo berhasil disimpan.');
    }

    public function straightenBalance(Request $request, BankAccount $bank_account): RedirectResponse
    {
        $data = $request->validate([
            'new_balance' => 'required|numeric|min:0',
            'reason' => 'required|string|max:1000',
        ], [
            'new_balance.required' => 'Saldo benar wajib diisi.',
            'reason.required' => 'Alasan meluruskan saldo wajib diisi.',
        ]);

        $oldBalance = (float) $bank_account->balance;
        $newBalance = (float) $data['new_balance'];
        $difference = abs($newBalance - $oldBalance);

        if ($difference < 0.01) {
            return back()->with('error', 'Saldo sudah sama, tidak ada perubahan yang perlu dicatat.');
        }

        DB::transaction(function () use ($bank_account, $oldBalance, $newBalance, $difference, $data) {
            $bank_account->update(['balance' => $newBalance]);

            BankBalanceAdjustment::create([
                'bank_account_id' => $bank_account->id,
                'previous_balance' => $oldBalance,
                'new_balance' => $newBalance,
                'difference' => $difference,
                'type' => $newBalance > $oldBalance ? 'increase' : 'decrease',
                'description' => 'Meluruskan saldo: ' . $data['reason'],
                'created_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Saldo berhasil diluruskan dan alasannya sudah tersimpan di riwayat meluruskan saldo.');
    }

    public function destroy(BankAccount $bank_account): JsonResponse
    {
        if ($bank_account->transactions()->exists() || BankTransfer::where('from_bank_account_id', $bank_account->id)->orWhere('to_bank_account_id', $bank_account->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Rekening sudah memiliki riwayat transaksi.'], 422);
        }

        $bank_account->delete();
        return response()->json(['success' => true]);
    }

    private function validatedData(Request $request, ?BankAccount $bankAccount = null): array
    {
        return $request->validate([
            'code' => 'required|string|max:20|unique:bank_accounts,code' . ($bankAccount ? ',' . $bankAccount->id : ''),
            'bank_name' => 'required|string|max:100',
            'account_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'balance' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
        ]);
    }
}
