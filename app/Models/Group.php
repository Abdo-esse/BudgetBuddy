<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'devise',
        'solde',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function expensesGroup()
    {
        return $this->hasMany(ExpenseGroup::class);
    }

    public static function validateGroup($group_id)
    {
        return self::find($group_id);
    }
}
