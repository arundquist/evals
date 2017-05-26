<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    //
    public function comment()
    {
        return $this->hasOne('App\Comment');
    }

    public function course()
    {
        return $this->belongsTo('App\Course');
    }
}
