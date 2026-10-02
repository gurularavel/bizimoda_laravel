<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    public const TYPES = ['contact' => 'Əlaqə forması', 'one_click' => 'Bir kliklə al'];

    protected $fillable = ['type', 'name', 'phone', 'email', 'subject', 'message', 'product_id', 'url', 'locale', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
