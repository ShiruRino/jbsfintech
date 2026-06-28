<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionCollection;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

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
        $transaction = Transaction::create([
            'user_id' => $request->user()->id,
            'account_id' => $request->validated('account_id'),
            'category_id' => $request->validated('category_id'),
            'type' => $request->validated('type'),
            'amount' => $request->validated('amount'),
            'transaction_date' => $request->validated('transaction_date', now()->toDateString()),
            'note' => $request->validated('note', null),
            'attachment_path' => $request->validated('attachment_path', null),
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
        if($transaction->user_id != $request->user()->id){
            throw new AuthorizationException();
        }
        $transaction->update($request->validated());
        $transaction->load('account', 'category');
        return $this->sendResponse(new TransactionResource($transaction), 'Transaction updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction, Request $request)
    {
        if($transaction->user_id != $request->user()->id){
            throw new AuthorizationException();
        }
        $transaction->delete();
        return $this->sendResponse(null, 'Transaction deleted successfully');
    }
}
