<?php

namespace App\Http\Controllers\Api\Point;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UserPointController extends Controller
{
    const CACHE_DURATION = 15; // بالدقائق

    protected function getActor()
    {
        return auth('web')->user() ?? auth('pharmacist')->user();
    }

    protected function getActorKey()
    {
        $actor = $this->getActor();
        if ($actor instanceof \App\Models\User) return ['user_id', $actor->id];
        if ($actor instanceof \App\Models\Pharmacist) return ['pharmacist_id', $actor->id];
        throw new \Exception('نوع المستخدم غير مدعوم');
    }

    public function getPointsSummary()
    {
        try {
            $actor = $this->getActor();
            [$column, $id] = $this->getActorKey();
            $cacheKey = "cache:points:summary:{$column}:{$id}";

            return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($column, $id) {
                $now = Carbon::now();

                $points = \App\Models\Point::where($column, $id)
                    ->selectRaw('type, SUM(amount) as total, COUNT(*) as count, MAX(created_at) as last_activity')
                    ->groupBy('type')
                    ->get()
                    ->keyBy('type');

                $expired = \App\Models\Point::where($column, $id)
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

    public function getEarnedPoints()
    {
        return $this->getPointsByType('earned', function ($query) {
            $query->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
        });
    }

    public function getSpentPoints()
    {
        return $this->getPointsByType('spent');
    }

    public function getExpiredPoints()
    {
        return $this->getPointsByType('earned', function ($query) {
            $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
        });
    }

    protected function getPointsByType($type, $additionalConditions = null)
    {
        try {
            [$column, $id] = $this->getActorKey();
            $cacheKey = "cache:points:{$type}:{$column}:{$id}";

            return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_DURATION), function () use ($column, $id, $type, $additionalConditions) {
                $query = \App\Models\Point::where($column, $id)
                    ->where('type', $type)
                    ->select(['id', 'amount', 'expires_at', 'created_at', 'source_name'])
                    ->orderBy('created_at', 'desc');

                if ($additionalConditions) {
                    $query->where($additionalConditions);
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

    protected function errorResponse($message = 'حدث خطأ غير متوقع', $code = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'code' => $code
        ], $code);
    }
}
