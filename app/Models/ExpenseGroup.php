<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseGroup extends Model
{
    use HasFactory;

    protected $table = 'expenses_groups';

    protected $fillable = ['group_id', 'title', 'total_prix', 'description', 'methode_division'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'expenses_users')->withPivot('montant_contribution', 'is_payer', 'pourcentage');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
