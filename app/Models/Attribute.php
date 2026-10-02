<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Attribute extends Model
{
    use HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = ['attribute_group_id', 'name', 'sort'];

    public function group()
    {
        return $this->belongsTo(AttributeGroup::class, 'attribute_group_id');
    }

    public function values()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort');
    }
}
