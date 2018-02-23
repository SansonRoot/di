<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pledge extends Model
{
    //
    protected $guarded = ['id'];

    public function setDateAttribute($value){
        $value = $this->updated_at;
    }

    public function setTimeAttribute($value){
        $value = $this->updated_at;
    }

    public function setMaturityAttribute($value){
        $value = $this->created_at;

    }
}
