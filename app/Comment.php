<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    public function score()
    {
        return $this->belongsTo('App\Score');
    }
}
