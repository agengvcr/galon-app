<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // ENUM debt_status
        if (!Schema::hasTable('debts')) {
            \DB::statement("CREATE TYPE debt_status AS ENUM ('UNPAID', 'PARTIALLY_PAID', 'PAID')");
        }
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->enum('status', ['UNPAID', 'PARTIALLY_PAID', 'PAID'])->default('UNPAID');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }
    public function down()
    {
        Schema::dropIfExists('debts');
        // Drop enum type if using PostgreSQL
        \DB::statement('DROP TYPE IF EXISTS debt_status');
    }
}; 