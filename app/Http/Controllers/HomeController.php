<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessUpload;
use App\Models\Upload;
use App\Support\PlatformStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class HomeController extends Controller
{
    public function index(): View
    {
        try {
            $uploads = Upload::query()->latest()->limit(12)->get();
        } catch (Throwable) {
            $uploads = collect(); // the database check says why
        }

        return view('welcome', [
            'checks' => PlatformStatus::all(),
            'uploads' => $uploads,
        ]);
    }

    /** A file to Object Storage, its record to PostgreSQL, a job to the worker. */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'max:10240']]);
        $file = $request->file('file');

        $path = Storage::disk('s3')->putFile('uploads', $file, 'public');
        if ($path === false) {
            return back()->withErrors(['file' => __('Object Storage did not take the file. See the service logs.')]);
        }

        $upload = Upload::create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
        ProcessUpload::dispatch($upload);

        return redirect()->route('home')->with('sent', $upload->original_name);
    }

    /** The same checks, for a monitor or a script. */
    public function status(): JsonResponse
    {
        $checks = PlatformStatus::all();
        $ok = collect($checks)->every(fn (array $c) => $c['ok']);

        return response()->json(['ok' => $ok, 'checks' => $checks], $ok ? 200 : 503);
    }
}
