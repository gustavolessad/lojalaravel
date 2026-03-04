<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = '/' . ltrim($request->path(), '/');

        $redirects = Cache::remember('seo_redirects', 300, function () {
            return Redirect::where('active', true)
                ->select('from_url', 'to_url', 'status_code', 'id')
                ->get()
                ->keyBy('from_url');
        });

        if ($redirect = $redirects->get($path)) {
            Redirect::withoutTimestamps(fn () =>
                Redirect::where('id', $redirect->id)->update([
                    'hits'        => \DB::raw('hits + 1'),
                    'last_hit_at' => now(),
                ])
            );

            return redirect($redirect->to_url, $redirect->status_code);
        }

        return $next($request);
    }
}
