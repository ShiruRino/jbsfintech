<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $initialBalance = (int) $this->initial_balance;
        $totalIncome = (int) ($this->total_income ?? 0);
        $totalExpense = (int) ($this->total_expense ?? 0);

        return [
            'name' => $this->name,
            'type' => $this->type,
            'initial_balance' => $initialBalance,
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $initialBalance + $totalIncome - $totalExpense,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
