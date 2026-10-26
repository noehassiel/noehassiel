<?php

namespace App\Models;

use Database\Factories\RequirementFileFactory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

class RequirementFile extends Model
{
    /** @use HasFactory<RequirementFileFactory> */
    use HasFactory;

    protected $fillable = [
        'project_requirement_id',
        'uploaded_by',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected static function booted(): void
    {
        // The stored file goes with its row
        static::deleted(fn (RequirementFile $file) => static::disk()->delete($file->path));
    }

    /**
     * Client uploads share the private disk with invoices (R2 in production).
     */
    public static function disk(): Filesystem
    {
        return Storage::disk(static::diskName());
    }

    public static function diskName(): string
    {
        return config('filesystems.invoices_disk');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(ProjectRequirement::class, 'project_requirement_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function humanSize(): string
    {
        return Number::fileSize($this->size, precision: 1);
    }
}
