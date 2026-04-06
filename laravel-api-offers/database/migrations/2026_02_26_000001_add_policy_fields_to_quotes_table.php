<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            // Keep original fields, add real ones
            $table->string('policy_id')->nullable()->after('id');
            $table->string('policy_number')->nullable()->after('policy_id');
            $table->string('offer_id')->nullable()->after('policy_number');
            $table->string('provider_code')->nullable()->after('insurer_name');
            $table->string('currency', 10)->default('RON')->after('price');
            $table->date('start_date')->nullable()->after('currency');
            $table->date('end_date')->nullable()->after('start_date');
            $table->tinyInteger('installment_count')->default(1)->after('end_date');
            $table->string('status', 50)->default('created')->after('installment_count');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'policy_id', 'policy_number', 'offer_id',
                'provider_code', 'currency', 'start_date',
                'end_date', 'installment_count', 'status'
            ]);
        });
    }
};
