<?php

namespace App\Jobs;

use App\Models\Upload;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

/**
 * Runs on the worker service: reads the file back from Object Storage and
 * marks it processed — where a real app would make a thumbnail or parse a
 * spreadsheet.
 */
class ProcessUpload implements ShouldQueue
{
    use Queueable;

    public function __construct(public Upload $upload)
    {
    }

    public function handle(): void
    {
        $size = Storage::disk('s3')->size($this->upload->path);

        $this->upload->forceFill([
            'size' => $size,
            'processed_at' => now(),
        ])->save();
    }
}
