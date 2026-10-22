<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_name',
        'phone',
        'address',
        'logo',
        'email',
        'rfc',
        'website',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reminded_at' => 'datetime',
        ];
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function invitations()
    {
        return $this->hasMany(ClientInvitation::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function proposals()
    {
        return $this->hasMany(Proposal::class);
    }
}
