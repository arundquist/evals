<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCourseInstructorPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_instructor', function (Blueprint $table) {
            $table->integer('course_id')->unsigned()->index();
//            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->integer('instructor_id')->unsigned()->index();
//            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('cascade');
            $table->primary(['course_id', 'instructor_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('course_instructor');
    }
}
