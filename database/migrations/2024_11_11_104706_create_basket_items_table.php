<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('basket_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basket_id')->index();
            $table->foreignId('product_id')->index();
            $table->integer('quantity');
            $table->string('description');

            $table->timestamps();

            $table->unique(['basket_id', 'product_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basket_items');
    }
};
