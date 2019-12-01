<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskHasParentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_has_parents', function (Blueprint $table) {
            $table->unsignedInteger('task_id');
            $table->unsignedInteger('parent_task_id');

            $table->foreign('task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');

            $table->foreign('parent_task_id')
                ->references('id')
                ->on('tasks')
                ->onDelete('cascade');

            $table->primary([
                'task_id',
                'parent_task_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_has_parents');
    }
}
