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
            'members' => 'nullable|array|min:1',
            'members.*' => 'exists:users,id|not_in:' . auth()->id(), // éviter que l'utilisateur ne se mette lui-même dans le groupe
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du groupe est obligatoire.',
            'name.unique' => 'Ce nom de groupe existe déjà.',
            'devise.required' => 'Veuillez spécifier la devise.',
            'devise.size' => 'La devise doit être un code de 3 lettres (ex: EUR, USD).',
            'members.required' => 'Vous devez ajouter au moins un membre.',
            'members.*.exists' => 'Certains membres n\'existent pas.',
        ];
    }
}
