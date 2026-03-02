<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'client_id',
        'start_date',
        'end_date',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
