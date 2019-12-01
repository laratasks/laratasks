<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');

            $table->string('task_type');
            $table->string('resource');
            $table->string('queue')->default('default');
            $table->string('status');
            $table->string('parent_strategy');

            $table->text('parameters')->default('{}');
            $table->text('message')->default('{}');

            $table->integer('priority');

            $table->unsignedInteger('max_attempts');
            $table->unsignedInteger('attempts_used')->default(0);
            $table->unsignedInteger('created_by')->nullable();

            $table->dateTime('scheduled_to');
            $table->dateTime('dispatched_at')->nullable();
            $table->dateTime('failed_at')->nullable();
            $table->dateTime('succeed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
