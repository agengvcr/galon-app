<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('debt_id');
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->foreign('debt_id')->references('id')->on('debts')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('debt_payments');
    }
}; 