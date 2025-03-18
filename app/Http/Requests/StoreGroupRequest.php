<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'devise' => 'required|string|size:3',
            'solde' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'members' => 'nullable|array|min:1',
            'members.*' => 'exists:users,id|not_in:' . auth()->id(), 
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du groupe est obligatoire.',
            'name.unique' => 'Ce nom de groupe existe déjà.',
            'devise.required' => 'Veuillez spécifier la devise.',
            'devise.size' => 'La devise doit être un code de 3 lettres (ex: EUR, USD).',
            'solde.numeric' => 'Le solde doit être un nombre.',
            'solde.min' => 'Le solde ne peut pas être négatif.',
            'solde.regex' => 'Le solde doit être un nombre valide avec jusqu’à 2 décimales (ex: 10, 10.5, 10.55).',
            'members.required' => 'Vous devez ajouter au moins un membre.',
            'members.*.exists' => 'Certains membres n\'existent pas.',
        ];
    }
}
