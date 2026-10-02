<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class AttributeGroup extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = ['name', 'sort'];

    public function attributes()
    {
        return $this->hasMany(Attribute::class)->orderBy('sort');
    }
}
