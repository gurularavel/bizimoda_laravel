<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class OptionValue extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = ['option_id', 'name', 'color', 'image', 'sort'];

    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
