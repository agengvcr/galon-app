<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->integer('galon_out');
            $table->integer('galon_in');
            $table->timestamp('transaction_date')->useCurrent();
            $table->decimal('total_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}; 