<?php

namespace App\Models;

use Database\Factories\ProjectLinkFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjectLink extends Model
{
    /** @use HasFactory<ProjectLinkFactory> */
    use HasFactory;

    /**
     * Known shared-folder hosts, matched against the end of the URL's host.
     *
     * @var array<string, string>
     */
    public const PROVIDERS = [
        'drive.google.com' => 'google_drive',
        'docs.google.com' => 'google_drive',
        'sharepoint.com' => 'sharepoint',
        'onedrive.live.com' => 'onedrive',
        '1drv.ms' => 'onedrive',
        'dropbox.com' => 'dropbox',
    ];

    protected $fillable = [
        'project_id',
        'label',
        'url',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Which storage service the link points to, or "other".
     */
    public function provider(): string
    {
        $host = Str::lower((string) parse_url($this->url, PHP_URL_HOST));

        foreach (self::PROVIDERS as $domain => $provider) {
            if ($host === $domain || Str::endsWith($host, ".{$domain}")) {
                return $provider;
            }
        }

        return 'other';
    }
}
