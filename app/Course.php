<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    //
    public function semester()
    {
        return $this->belongsTo('App\Semester');
    }

    public function instructors()
    {
        return $this->belongsToMany('App\Instructor');
    }

    public function gencomments()
    {
        return $this->hasMany('App\Gencomment');
    }

    public function scores()
    {
        return $this->hasMany('App\Score');
    }
}
