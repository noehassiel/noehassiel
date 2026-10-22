<?php

namespace App\Models;

use Database\Factories\SpotifyAccountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * The single linked Spotify account behind the dock player. Lives in the database because Laravel Cloud's disk resets on every deploy.
 */
class SpotifyAccount extends Model
{
    /** @use HasFactory<SpotifyAccountFactory> */
    use HasFactory;

    protected $fillable = ['refresh_token'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'refresh_token' => 'encrypted',
        ];
    }
}
