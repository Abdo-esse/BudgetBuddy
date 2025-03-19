<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('expenses_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_group_id')->constrained('expenses_groups'); 
            $table->foreignId('user_id')->constrained(); 
            $table->decimal('montant_contribution', 10, 2); 
            $table->boolean('is_payer')->default(false); 
            $table->decimal('pourcentage', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses_users');
    }
};
