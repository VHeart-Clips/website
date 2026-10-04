<?php

declare(strict_types=1);

namespace App\Filament\AdminPanel\Widgets;

use App\Enums\Filament\LucideIcon;
use Exception;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Number;
use Throwable;

class DashboardSpeidyBucketStatisticWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected ?string $heading = 'Speidy\'s Super Storage (S3)';

    public static function canView(): bool
    {
        return auth()->user()->getRole()?->id === 0;
    }

    protected function getStats(): array
    {
        if (empty(config('vheart.clips.s3-storage.token')) || empty(config('vheart.clips.s3-storage.remote'))) {
            return [
                Stat::make('Bucket Statistics', 'Not Configured')
                    ->description('Storage has not been configured yet.')
                    ->descriptionIcon(LucideIcon::TriangleAlert)
                    ->color('warning'),
            ];
        }

        $data = Cache::remember('s3_bucket_info', now()->addMinutes(5), static function () {
            try {
                $http = Http::timeout(1)
                    ->withHeader('Authorization', 'Bearer '.config('vheart.clips.s3-storage.token'));

                $response = retry(
                    times: 3,
                    callback: static fn () => $http->get(config('vheart.clips.s3-storage.remote').'/v2/GetBucketInfo?id='.config('vheart.clips.s3-storage.bucket_id')),
                    sleepMilliseconds: 200,
                    when: static fn (Throwable $e): bool => $e instanceof ConnectionException,
                );

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (Exception $exception) {
                report($exception);
            }

            return null;
        });

        if (! $data) {
            return [
                Stat::make('Bucket Statistics', 'Error')
                    ->description('Could not connect to S3 Server')
                    ->descriptionIcon(LucideIcon::TriangleAlert)
                    ->color('danger'),
            ];
        }

        $objects = $data['objects'] ?? 0;
        $bytes = $data['bytes'] ?? 0;
        $maxSize = $data['quotas']['maxSize'] ?? 0;
        $unfinishedUploads = $data['unfinishedUploads'] ?? 0;
        $unfinishedMultipart = $data['unfinishedMultipartUploads'] ?? 0;
        $unfinishedParts = $data['unfinishedMultipartUploadParts'] ?? 0;
        $unfinishedBytes = $data['unfinishedMultipartUploadBytes'] ?? 0;

        $quotaText = 'Unlimited';
        $quotaColor = 'success';
        if ($maxSize > 0) {
            $percentage = round(($bytes / $maxSize) * 100, 1);
            $quotaText = "$percentage% / ".Number::fileSize($maxSize, precision: 1);

            if ($percentage >= 90) {
                $quotaColor = 'danger';
            } elseif ($percentage >= 75) {
                $quotaColor = 'warning';
            }
        }

        $totalUnfinishedCount = $unfinishedUploads + $unfinishedMultipart;

        if ($totalUnfinishedCount > 0 || $unfinishedBytes > 0) {
            $formattedBytes = Number::fileSize($unfinishedBytes, precision: 2);
            $uploadStatus = "$totalUnfinishedCount active ($unfinishedParts parts - $formattedBytes)";
            $uploadColor = 'warning';
        } else {
            $uploadStatus = null;
            $uploadColor = 'success';
        }

        return [
            Stat::make('Storage Used', Number::fileSize($bytes, precision: 2))
                ->description($quotaText)
                ->color($quotaColor),

            Stat::make('Total Objects', Number::format($objects))
                ->color('success'),

            Stat::make('Unfinished Uploads', Number::format($totalUnfinishedCount))
                ->description($uploadStatus)
                ->color($uploadColor),
        ];
    }
}
