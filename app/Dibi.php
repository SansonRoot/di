<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dibi extends Model
{
    //

    protected $guarded = ['id'];

    public function getWalletAttribute($value){
        return Wallet::find($value);
    }

}
