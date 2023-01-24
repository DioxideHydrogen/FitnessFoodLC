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
        Schema::create('cron_jobs_taks', function (Blueprint $table) {
            $table->id();
			$table->string("file");
			$table->string("message")->nullable();
			$table->enum("status", ['success', 'processing', 'error']);
			$table->foreignId('cron_job_id')->references('id')->on('cron_jobs');
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
        Schema::dropIfExists('cron_jobs_taks');
    }
};
