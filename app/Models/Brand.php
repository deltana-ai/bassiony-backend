<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\Model;

class Brand extends BaseModel
{
    use HasMedia;
    protected $with = [
        'media',
    ];
    protected $casts = [
        'active' => 'boolean',
        'show_home' => 'boolean',
    ];
    protected $guarded = ['id'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
