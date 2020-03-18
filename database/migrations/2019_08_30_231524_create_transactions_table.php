<?php

use App\Services\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('destination');
            $table->string('service_code');
            $table->string('internal_id');
            $table->string('external_id');
            $table->enum('status', [
                Constants::QUEUED,
                Constants::PROCESSING,
                Constants::SUCCESS,
                Constants::FAILED
            ]);
            $table->string('energy')->nullable();
            $table->string('amount')->nullable();
            $table->string('token')->nullable();
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
        Schema::dropIfExists('transactions');
    }
}
