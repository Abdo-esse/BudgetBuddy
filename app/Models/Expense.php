<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

      /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'amount',
        'currency',
        'description',
    ];

    public function user()
    {
        return $this->belongsto(User::class);
    }

    public function tags()
{
    return $this->belongsToMany(Tag::class, 'expense_tag');
}
}
