<?php

namespace App\Http\Controllers\Common\Point;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UserPointController extends Controller
{
    const CACHE_DURATION = 15; // بالدقائق

    /**
     * ملخص النقاط
     */
    public function getPointsSummary()
    {
        try {
            $auth = auth()->user();
            $relation = $this->getRelationName($auth);
            $cacheKey = "cache:points:summary:{$relation}:{$auth->id}";

            return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($auth, $relation) {
                $now = Carbon::now();

                $points = $auth->points()
                    ->selectRaw('type, SUM(amount) as total, COUNT(*) as count, MAX(created_at) as last_activity')
                    ->groupBy('type')
                    ->get()
                    ->keyBy('type');

                $expired = $auth->points()
                    ->where('type', 'earned')
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<=', $now)
                    ->selectRaw('SUM(amount) as total, COUNT(*) as count')
                    ->first();

                return response()->json([
                    'status' => true,
                    'data' => [
                        'earned' => [
                            'total' => $points['earned']->total ?? 0,
                            'count' => $points['earned']->count ?? 0,
                            'last_activity' => $points['earned']->last_activity ?? null,
                        ],
                        'spent' => [
                            'total' => $points['spent']->total ?? 0,
                            'count' => $points['spent']->count ?? 0,
                            'last_activity' => $points['spent']->last_activity ?? null,
                        ],
                        'expired' => [
                            'total' => $expired->total ?? 0,
                            'count' => $expired->count ?? 0,
                        ],
                        'available' => max(0, ($points['earned']->total ?? 0) - ($points['spent']->total ?? 0) - ($expired->total ?? 0)),
                    ],
                    'meta' => [
                        'cache' => true,
                        'generated_at' => now()->toDateTimeString(),
                    ]
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Points summary error: " . $e->getMessage());
            return $this->errorResponse();
        }
    }

    /**
     * النقاط المكتسبة (صالحة فقط)
     */
    public function getEarnedPoints()
    {
        return $this->getPointsByType('earned', function ($query) {
            $query->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
        }, 'valid');
    }

    /**
     * النقاط المنتهية
     */
    public function getExpiredPoints()
    {
        return $this->getPointsByType('earned', function ($query) {
            $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
        }, 'expired');
    }

    /**
     * النقاط المنفقة
     */
    public function getSpentPoints()
    {
        return $this->getPointsByType('spent', null, 'all');
    }

    /**
     * جلب النقاط حسب النوع
     */
    protected function getPointsByType($type, $additionalConditions = null, $suffix = '')
    {
        try {
            $auth = auth()->user();
            $relation = $this->getRelationName($auth);
            $keySuffix = $suffix ? ":{$suffix}" : '';
            $cacheKey = "cache:points:{$relation}:{$type}:{$auth->id}{$keySuffix}";

            return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($auth, $type, $additionalConditions) {
                $query = $auth->points()
                    ->where('type', $type)
                    ->select(['id', 'amount', 'expires_at', 'created_at', 'source_name'])
                    ->orderBy('created_at', 'desc');

                if (is_callable($additionalConditions)) {
                    $additionalConditions($query);
                }

                $points = $query->paginate(15);

                return response()->json([
                    'status' => true,
                    'data' => [
                        'points' => $points->items(),
                        'summary' => [
                            'total' => $points->sum('amount'),
                            'count' => $points->total(),
                        ]
                    ],
                    'meta' => [
                        'pagination' => [
                            'current_page' => $points->currentPage(),
                            'total_pages' => $points->lastPage(),
                            'per_page' => $points->perPage(),
                        ],
                        'cache' => true,
                        'generated_at' => now()->toDateTimeString()
                    ]
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Points by type error [{$type}]: " . $e->getMessage());
            return $this->errorResponse();
        }
    }

    /**
     * تحديد العلاقة بناء على نوع المستخدم (User أو Pharmacist)
     */
    protected function getRelationName($auth)
    {
        if (method_exists($auth, 'getTable')) {
            return $auth->getTable(); // users أو pharmacists
        }

        return 'unknown';
    }

    /**
     * استجابة خطأ عامة
     */
    protected function errorResponse($message = 'حدث خطأ غير متوقع', $code = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'code' => $code
        ], $code);
    }
}
