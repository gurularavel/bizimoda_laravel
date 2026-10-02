<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderHistory extends Model
{
    protected $fillable = ['order_id', 'status', 'comment', 'admin_id'];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
