<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends BaseModel
{
    protected $fillable = [
        'user_id', 'pharmacist_id', 'company_id', 'type',
        'amount', 'source_name', 'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pharmacist()
    {
        return $this->belongsTo(Pharmacist::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
