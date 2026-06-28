<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
    */
    public function index(Request $request)
    {
        $accounts = $request->user()
            ->accounts()
            ->select([
                'id',
                'name',
                'type',
                'initial_balance',
                'is_active',
            ])
            ->where('is_active', true)
            ->withSum([
                'transactions as total_income' => function ($query) {
                    $query
                        ->where('type', 'income')
                        ->where('is_active', true); // remove if transactions has no is_active column
                },
            ], 'amount')
            ->withSum([
                'transactions as total_expense' => function ($query) {
                    $query
                        ->where('type', 'expense')
                        ->where('is_active', true); // remove if transactions has no is_active column
                },
            ], 'amount')
            ->orderBy('name')
            ->get();
        $data = AccountResource::collection($accounts);
        return $this->sendResponse($data,'Accounts retrieved successfully');
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountRequest $request)
    {
        $account = Account::create([
            'user_id' => $request->user()->id,
            'name' => $request->validated('name'),
            'type' => $request->validated('type'),
            'initial_balance' => $request->validated('initial_balance'),
            'is_active' => $request->validated('is_active') ?? true,
        ]);
        return $this->sendResponse(new AccountResource($account),'Account registered successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account, Request $request)
    {
        if($account->user_id != $request->user()->id){
            throw new AuthorizationException();
        }
        $data = [];
        $data['account'] = new AccountResource($account);
        $data['latest_transactions'] = $account->transactions()->latest()->limit(3);
        return $this->sendResponse($data, 'Account retrieved successfully'); 
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(AccountRequest $request, Account $account)
    {
        $account->update($request->validated());
        return $this->sendResponse(new AccountResource($account), 'Account updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $account->transactions()->delete();
        $account->delete();
        return $this->sendResponse(null, 'Account and related transactions successfully deleted');
    }
}
