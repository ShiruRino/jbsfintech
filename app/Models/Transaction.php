<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
    public function account(){
        return $this->belongsTo(Account::class, 'account_id');
    }
    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }
}
