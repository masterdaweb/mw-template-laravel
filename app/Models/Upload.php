<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A file sent from the home page: kept in Object Storage (the s3 disk),
 * described here in PostgreSQL.
 */
class Upload extends Model
{
    protected $fillable = ['path', 'original_name', 'mime_type', 'size', 'processed_at'];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    /** Its public address: the bucket is public, so the link works for anyone. */
    public function url(): string
    {
        return Storage::disk('s3')->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
