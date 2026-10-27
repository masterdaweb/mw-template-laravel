@php
    $labels = [
        'database' => 'PostgreSQL',
        'redis' => 'Redis',
        'storage' => 'Object Storage',
        'queue' => __('Queue (worker)'),
        'scheduler' => __('Scheduler'),
    ];
    $waiting = $uploads->contains(fn ($u) => $u->processed_at === null);
    $size = fn (int $bytes) => $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : max(1, round($bytes / 1024)).' KB';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if ($waiting)
        {{-- A file waits for the worker: look again in a moment. --}}
        <meta http-equiv="refresh" content="4">
    @endif
    <title>{{ config('app.name') }}</title>
    <style>
        :root { --bg: #f7f7f8; --card: #fff; --text: #18181b; --muted: #71717a; --line: #e4e4e7; --ok: #16a34a; --bad: #dc2626; --accent: #f53003; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #0f0f11; --card: #18181b; --text: #f4f4f5; --muted: #a1a1aa; --line: #27272a; }
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--text); font: 15px/1.55 ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { max-width: 860px; margin: 0 auto; padding: 40px 16px 64px; }
        .brand { color: var(--muted); font-size: 13px; letter-spacing: .02em; }
        h1 { font-size: 30px; line-height: 1.2; margin: 6px 0 8px; }
        h2 { font-size: 17px; margin: 0 0 14px; }
        p.lead { color: var(--muted); margin: 0 0 28px; max-width: 640px; }
        section { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 20px; margin-bottom: 16px; }
        ul.checks { list-style: none; padding: 0; margin: 0; }
        ul.checks li { display: flex; gap: 12px; align-items: baseline; padding: 8px 0; border-top: 1px solid var(--line); }
        ul.checks li:first-child { border-top: 0; }
        .dot { width: 9px; height: 9px; border-radius: 50%; flex: none; transform: translateY(-1px); }
        .dot.ok { background: var(--ok); } .dot.bad { background: var(--bad); }
        .name { font-weight: 600; min-width: 150px; }
        .detail { color: var(--muted); overflow-wrap: anywhere; }
        .detail.bad { color: var(--bad); }
        form { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        input[type=file] { flex: 1 1 260px; color: var(--muted); }
        button { background: var(--accent); color: #fff; border: 0; border-radius: 8px; padding: 9px 16px; font: inherit; font-weight: 600; cursor: pointer; }
        .note { margin: 12px 0 0; padding: 10px 12px; border-radius: 8px; font-size: 14px; }
        .note.ok { background: color-mix(in srgb, var(--ok) 12%, transparent); }
        .note.bad { background: color-mix(in srgb, var(--bad) 12%, transparent); color: var(--bad); }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; margin-top: 16px; }
        .file { border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
        .thumb { aspect-ratio: 4 / 3; background: var(--bg); display: grid; place-items: center; color: var(--muted); font-size: 13px; }
        .thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .meta { padding: 8px 10px; font-size: 13px; }
        .meta a { color: inherit; font-weight: 600; text-decoration: none; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .meta span { color: var(--muted); }
        ol { margin: 0; padding-left: 20px; } ol li { margin: 6px 0; }
        code { font: 13px ui-monospace, SFMono-Regular, Menlo, monospace; background: var(--bg); padding: 1px 5px; border-radius: 4px; }
        footer { color: var(--muted); font-size: 13px; margin-top: 24px; }
    </style>
</head>
<body>
<main>
    <div class="brand">Laravel · Master da Web</div>
    <h1>{{ __('Your Laravel app is live') }}</h1>
    <p class="lead">{{ __('Everything below is already wired: the database, Redis, the files on Object Storage, the queue worker and the scheduler.') }}</p>

    <section aria-labelledby="status">
        <h2 id="status">{{ __('What this app reaches') }}</h2>
        <ul class="checks" data-testid="checks">
            @foreach ($checks as $key => $check)
                <li data-check="{{ $key }}" data-ok="{{ $check['ok'] ? 'true' : 'false' }}">
                    <span class="dot {{ $check['ok'] ? 'ok' : 'bad' }}" aria-hidden="true"></span>
                    <span class="name">{{ $labels[$key] }}</span>
                    <span class="detail {{ $check['ok'] ? '' : 'bad' }}">{{ $check['detail'] }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    <section aria-labelledby="files">
        <h2 id="files">{{ __('Send a file') }}</h2>
        <form method="post" action="{{ route('uploads.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" required aria-label="{{ __('File') }}">
            <button type="submit">{{ __('Send to Object Storage') }}</button>
        </form>
        @if (session('sent'))
            <p class="note ok" role="status">{{ __(':name was saved. The worker reads it back in a moment.', ['name' => session('sent')]) }}</p>
        @endif
        @error('file')
            <p class="note bad" role="alert">{{ $message }}</p>
        @enderror
        @if ($uploads->isNotEmpty())
            <div class="grid" data-testid="uploads">
                @foreach ($uploads as $upload)
                    <div class="file" data-processed="{{ $upload->processed_at ? 'true' : 'false' }}">
                        <div class="thumb">
                            @if ($upload->isImage())
                                <img src="{{ $upload->url() }}" alt="{{ $upload->original_name }}" loading="lazy">
                            @else
                                {{ strtoupper(pathinfo($upload->original_name, PATHINFO_EXTENSION) ?: 'file') }}
                            @endif
                        </div>
                        <div class="meta">
                            <a href="{{ $upload->url() }}" target="_blank" rel="noopener">{{ $upload->original_name }}</a>
                            <span>{{ $size($upload->size) }} · {{ $upload->processed_at ? __('read back by the worker') : __('in the queue…') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section aria-labelledby="next">
        <h2 id="next">{{ __('Next steps') }}</h2>
        <ol>
            <li>{{ __('Make the code yours: in the console, open this project and follow “Your code” to copy it to your GitHub and connect it.') }}</li>
            <li>{!! __('Change :routes and :view, then push: every push deploys.', ['routes' => '<code>routes/web.php</code>', 'view' => '<code>resources/views/welcome.blade.php</code>']) !!}</li>
            <li>{{ __('Add your own domain on the web service, under Domains.') }}</li>
        </ol>
    </section>

    <footer>Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }} · <a href="{{ route('status') }}" style="color: inherit">status.json</a></footer>
</main>
</body>
</html>
