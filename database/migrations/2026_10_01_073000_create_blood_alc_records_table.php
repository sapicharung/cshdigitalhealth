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
        Schema::create('blood_alc_records', function (Blueprint $table) {
            $table->id();
            $table->integer('order_no')->nullable()->index();
            $table->string('month_year', 50)->nullable()->index();
            $table->string('test_day', 50)->nullable();
            $table->string('hn', 50)->nullable()->index();
            $table->string('patient_name', 255);
            $table->string('police_station', 255)->nullable()->index();
            $table->string('police_officer', 255)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('doc_k8', 50)->nullable()->default(null);
            $table->string('doc_pher', 50)->nullable()->default(null);
            $table->string('doc_lab_send', 50)->nullable()->default(null);
            $table->string('lab_send_date', 100)->nullable();
            $table->string('hosxp_result_date', 100)->nullable();
            $table->string('claim_status', 100)->nullable()->index();
            $table->string('claim_date', 100)->nullable();
            $table->string('payment_received_date', 100)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_alc_records');
    }
};
