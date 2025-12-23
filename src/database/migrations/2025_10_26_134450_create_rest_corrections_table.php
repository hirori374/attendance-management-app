<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rest_corrections', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_batch_id')->nullable()->index();
            $table->date('request_date');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('rest_id')->nullable()->constrained()->cascadeOnDelete();
            $table->time('rest_request_start_time')->nullable();
            $table->time('rest_request_end_time')->nullable();
            $table->text('remarks');
            $table->string('status')->default('承認待ち');
            $table->timestamp('created_at')->useCurrent()->nullable();
            $table->timestamp('updated_at')->useCurrent()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rest_corrections');
    }
}
