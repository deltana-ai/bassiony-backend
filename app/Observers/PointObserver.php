<?php
namespace App\Observers;

use App\Models\Point;
use Illuminate\Support\Facades\Cache;

class PointObserver
{
    protected function clearCache(Point $point)
    {
        $keys = [];

        if ($point->user_id) {
            $id = $point->user_id;
            $keys = [
                "cache:points:summary:user_id:{$id}",
                "cache:points:earned:user_id:{$id}",
                "cache:points:spent:user_id:{$id}",
                "cache:points:expired:user_id:{$id}",
            ];
        } elseif ($point->pharmacist_id) {
            $id = $point->pharmacist_id;
            $keys = [
                "cache:points:summary:pharmacist_id:{$id}",
                "cache:points:earned:pharmacist_id:{$id}",
                "cache:points:spent:pharmacist_id:{$id}",
                "cache:points:expired:pharmacist_id:{$id}",
            ];
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function created(Point $point)
    {
        $this->clearCache($point);
    }

    public function updated(Point $point)
    {
        $this->clearCache($point);
    }

    public function deleted(Point $point)
    {
        $this->clearCache($point);
    }
}
// Register the observer in AppServiceProvider or a dedicated service provider
// app/Providers/AppServiceProvider.php                     
