<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $transactions = $request->user()->transactions()->with('account')->with('category')->latest()->paginate(10);
        return $this->sendResponse(new TransactionCollection($transactions), 'Transactions retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TransactionRequest $request)
    {
        $path = null;
        $file = $request->file('attachment');
        if(!empty($file)){
            $path = $file->store('transactions', 'public');
        }
        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'account_id' => $request->validated('account_id'),
            'category_id' => $request->validated('category_id'),
            'type' => $request->validated('type'),
            'amount' => $request->validated('amount'),
            'transaction_date' => $request->validated('transaction_date', now()->toDateString()),
            'note' => $request->validated('note', null),
            'attachment_path' => $path ?? null,
        ]);
        $transaction->load('account', 'category');
        return $this->sendResponse(new TransactionResource($transaction), 'Transaction added successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction, Request $request)
    {
        if($transaction->user_id != $request->user()->id){
            throw new AuthorizationException();
        }
        $transaction->load('account', 'category');
        return $this->sendResponse(new TransactionResource($transaction), 'Transaction retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TransactionRequest $request, Transaction $transaction)
    {
        if ((int) $transaction->user_id !== (int) $request->user()->id) {
            throw new AuthorizationException();
        }

        $validated = $request->validated();

        $oldPath = $transaction->attachment_path;
        $attachmentPath = $oldPath;
        $newUploadedPath = null;

        try {
            DB::transaction(function () use (
                $request,
                $transaction,
                $validated,
                &$attachmentPath,
                &$newUploadedPath
            ) {
                // Upload attachment pengganti bila ada file baru.
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');

                    if (!$file->isValid()) {
                        abort(422, 'Attachment tidak valid.');
                    }

                    $newUploadedPath = $file->store('transactions', 'public');
                    $attachmentPath = $newUploadedPath;
                }

                // Hapus attachment hanya bila frontend mengirim remove_attachment=true.
                if ($request->boolean('remove_attachment')) {
                    $attachmentPath = null;
                }

                $transaction->update([
                    'account_id' => $validated['account_id'] ?? $transaction->account_id,
                    'category_id' => $validated['category_id'] ?? $transaction->category_id,
                    'type' => $validated['type'] ?? $transaction->type,
                    'amount' => $validated['amount'] ?? $transaction->amount,
                    'transaction_date' => $validated['transaction_date'] ?? $transaction->transaction_date,
                    'note' => array_key_exists('note', $validated)
                        ? $validated['note']
                        : $transaction->note,
                    'attachment_path' => $attachmentPath,
                ]);
            });
        } catch (Throwable $e) {
            // Bersihkan file baru jika proses database gagal.
            if ($newUploadedPath) {
                Storage::disk('public')->delete($newUploadedPath);
            }

            throw $e;
        }

        // Hapus attachment lama hanya setelah update database sukses.
        if ($oldPath && $oldPath !== $attachmentPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $transaction->load('account', 'category');

        return $this->sendResponse(
            new TransactionResource($transaction),
            'Transaction updated successfully'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction, Request $request)
    {
        if($transaction->user_id != $request->user()->id){
            throw new AuthorizationException();
        }
        Storage::disk('public')->delete($transaction->attachment_path);
        $transaction->delete();
        return $this->sendResponse(null, 'Transaction deleted successfully');
    }
}
