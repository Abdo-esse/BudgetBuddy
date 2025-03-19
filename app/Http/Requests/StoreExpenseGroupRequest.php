<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'expense_group.title' => 'required|string|max:255',
            'expense_group.total_prix' => 'required|numeric',
            'expense_group.description' => 'required|string',
            'expense_group.methode_division' => 'required|in:égal,pourcentage',
            'expenses_users.*.user_id' => 'required|exists:users,id',
            'expenses_users.*.montant_contribution' => 'nullable|numeric',
            'expenses_users.*.is_payer' => 'required|boolean',
            'expenses_users.*.pourcentage' => 'nullable|numeric|min:0|max:100',
        ];
    }
}
