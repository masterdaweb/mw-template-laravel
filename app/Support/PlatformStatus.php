<?php

namespace App\Support;

use App\Models\Upload;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * What the home page shows: each part of the project, and whether this
 * service reaches it. Every check catches its own failure, so one part
 * down never hides the others.
 */
class PlatformStatus
{
    public const SCHEDULER_KEY = 'starter:scheduler_ran_at';

    /** @return array<string, array{ok: bool, detail: string}> */
    public static function all(): array
    {
        return [
            'database' => self::check(function () {
                $version = (string) DB::scalar('select version()');

                return implode(' ', array_slice(explode(' ', $version), 0, 2));
            }),
            'redis' => self::check(function () {
                $info = Redis::connection()->command('info', ['server']);
                $info = $info['Server'] ?? $info; // the shape phpredis gives, either way

                return isset($info['valkey_version'])
                    ? 'Valkey '.$info['valkey_version']
                    : 'Redis '.($info['redis_version'] ?? '');
            }),
            'storage' => self::check(function () {
                $bucket = (string) config('filesystems.disks.s3.bucket');
                if ($bucket === '') {
                    throw new \RuntimeException(__('No bucket connected (AWS_BUCKET is empty)'));
                }
                Storage::disk('s3')->getClient()->headBucket(['Bucket' => $bucket]);

                return __('Bucket :name', ['name' => $bucket]);
            }),
            'queue' => self::check(function () {
                $last = Upload::query()->whereNotNull('processed_at')->max('processed_at');

                return $last
                    ? __('Last job :when', ['when' => self::ago($last)])
                    : __('Waiting for the first job: send a file below');
            }),
            'scheduler' => self::check(function () {
                $last = Cache::get(self::SCHEDULER_KEY);

                return $last
                    ? __('Last run :when', ['when' => self::ago($last)])
                    : __('Waiting for the first run (every minute)');
            }),
        ];
    }

    /** "2 minutes ago", "just now", in the page's language. */
    private static function ago(string $at): string
    {
        return Carbon::parse($at)->diffForHumans(['options' => CarbonInterface::JUST_NOW]);
    }

    /** @return array{ok: bool, detail: string} */
    private static function check(callable $probe): array
    {
        try {
            return ['ok' => true, 'detail' => (string) $probe()];
        } catch (Throwable $e) {
            // The message, not the trace: never a password from a DSN.
            return ['ok' => false, 'detail' => self::short($e->getMessage())];
        }
    }

    private static function short(string $message): string
    {
        $line = strtok($message, "\n") ?: $message;
        $line = preg_replace('#[a-z]+://[^\s@]+@#i', '***@', $line) ?? $line;

        return mb_strimwidth($line, 0, 160, '…');
    }
}
