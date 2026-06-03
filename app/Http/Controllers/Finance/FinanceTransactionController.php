<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\FinanceItem;
use App\Models\FinanceTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FinanceTransactionController extends Controller
{
    public function index(): View
    {
        $data = FinanceTransaction::with(['item.category', 'bankAccount'])->latest('transaction_date')->latest('id')->get();
        $items = FinanceItem::with('category')->where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('bank_name')->get();
        return view('finance.transactions.index', compact('data', 'items', 'bankAccounts'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data) {
            $item = FinanceItem::with('category')->findOrFail($data['finance_item_id']);
            $bank = BankAccount::lockForUpdate()->findOrFail($data['bank_account_id']);
            $this->applyDelta($bank, $this->delta($item, (float) $data['amount']));

            FinanceTransaction::create($data + [
                'transaction_number' => $this->transactionNumber(),
                'created_by' => auth()->id(),
            ]);
        });

        return response()->json(['success' => true]);
    }

    public function edit(FinanceTransaction $finance_transaction): JsonResponse
    {
        return response()->json($finance_transaction);
    }

    public function update(Request $request, FinanceTransaction $finance_transaction): JsonResponse
    {
        $data = $this->validatedData($request);

        DB::transaction(function () use ($data, $finance_transaction) {
            $finance_transaction->load('item.category');
            $oldBank = BankAccount::lockForUpdate()->findOrFail($finance_transaction->bank_account_id);
            $oldDelta = $this->delta($finance_transaction->item, (float) $finance_transaction->amount);
            $this->applyDelta($oldBank, -$oldDelta);

            $newItem = FinanceItem::with('category')->findOrFail($data['finance_item_id']);
            $newBank = $oldBank->id === (int) $data['bank_account_id']
                ? $oldBank->refresh()
                : BankAccount::lockForUpdate()->findOrFail($data['bank_account_id']);
            $this->applyDelta($newBank, $this->delta($newItem, (float) $data['amount']));

            $finance_transaction->update($data);
        });

        return response()->json(['success' => true]);
    }

    public function destroy(FinanceTransaction $finance_transaction): JsonResponse
    {
        DB::transaction(function () use ($finance_transaction) {
            $finance_transaction->load('item.category');
            $bank = BankAccount::lockForUpdate()->findOrFail($finance_transaction->bank_account_id);
            $this->applyDelta($bank, -$this->delta($finance_transaction->item, (float) $finance_transaction->amount));
            $finance_transaction->delete();
        });

        return response()->json(['success' => true]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'transaction_date' => 'required|date',
            'finance_item_id' => 'required|exists:finance_items,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string',
        ]);
    }

    private function delta(FinanceItem $item, float $amount): float
    {
        return $item->category->type === 'income' ? $amount : -$amount;
    }

    private function applyDelta(BankAccount $bank, float $delta): void
    {
        $newBalance = (float) $bank->balance + $delta;
        if ($newBalance < 0) {
            throw ValidationException::withMessages(['amount' => 'Saldo rekening tidak mencukupi untuk transaksi ini.']);
        }
        $bank->update(['balance' => $newBalance]);
    }

    private function transactionNumber(): string
    {
        return 'KEU-' . now()->format('Ymd-His') . '-' . strtoupper(substr(uniqid(), -4));
    }
}
