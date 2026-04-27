<?php

declare(strict_types=1);

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Number;

class OctaneDebugController extends Controller
{
    public function __invoke(Request $request)
    {
        abort_unless($request->user()->isSuperAdmin(), 404);

        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        return response()->json([
            'worker' => [
                'pid' => getmypid(),
                'memory_usage' => [
                    'raw' => $memoryUsage,
                    'readable' => Number::fileSize($memoryUsage),
                ],
                'memory_peak' => [
                    'raw' => $memoryPeak,
                    'readable' => Number::fileSize($memoryPeak),
                ],
                'memory_limit' => ini_get('memory_limit'),
            ],
            'request_isolation' => [
                'request_instance_id' => spl_object_id(request()),
                'app_instance_id' => spl_object_id(app()),
                'request_class' => request()::class,
            ],
            'octane' => [
                'concurrency' => config('octane.max_execution_time'),
                'server' => config('octane.server'),
                'listeners' => array_keys(config('octane.listeners', [])),
            ],
            'opcache' => function_exists('opcache_get_status') && opcache_get_status(false) ? [
                'enabled' => opcache_get_status(false)['opcache_enabled'] ?? false,
                'hit_rate' => round(opcache_get_status(false)['opcache_statistics']['opcache_hit_rate'] ?? 0, 2),
                'cached_scripts' => opcache_get_status(false)['opcache_statistics']['num_cached_scripts'] ?? 0,
                'memory_used' => opcache_get_status(false)['memory_usage']['used_memory'] ?? 0,
                'memory_free' => opcache_get_status(false)['memory_usage']['free_memory'] ?? 0,
                'jit_enabled' => opcache_get_status(false)['jit']['enabled'] ?? false,
                'jit_buffer_size' => ini_get('opcache.jit_buffer_size'),
            ] : null,
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'extensions' => get_loaded_extensions(),
            ],
            'app' => [
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'timezone' => config('app.timezone'),
                'locale' => app()->getLocale(),
            ],
            'services' => [
                'cache_driver' => config('cache.default'),
                'queue_driver' => config('queue.default'),
                'session_driver' => config('session.driver'),
                'db_driver' => config('database.default'),
            ],
            'db' => [
                'connected' => (static function (): true|string {
                    try {
                        DB::connection()->getPdo();

                        return true;
                    } catch (Exception $e) {
                        return $e->getMessage();
                    }
                })(),
            ],
            'redis' => [
                'connected' => (static function (): true|string {
                    try {
                        Redis::ping();

                        return true;
                    } catch (Exception $e) {
                        return $e->getMessage();
                    }
                })(),
            ],
        ]);
    }
}
