<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'expense_group' => [
                'title' => $this->title,
                'total_prix' => $this->total_prix,
                'description' => $this->description,
                'methode_division' => $this->methode_division,
            ],
            'expenses_users' => $this->users->map(function ($user) {
                return [
                    'user_id' => $user->id,
                    'montant_contribution' => $user->pivot->montant_contribution ?? null,
                    'is_payer' => $user->pivot->is_payer,
                ];
            }),
        ];
    }
}
