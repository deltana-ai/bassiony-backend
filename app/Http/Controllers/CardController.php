<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponse;
use App\Http\Resources\CartItemResource;
use App\Models\Card;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CartItem;
use Exception;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function index()
    {
        try {
            $cards = Card::where('user_id', Auth::id())->get();
            return $cards;
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'card_holder_name' => 'nullable|string|max:255',
                'card_number'      => 'nullable|string|max:255',
                'expiry_date'      => 'nullable|string|max:255',
                'cvv'              => 'nullable|string|max:10',
            ]);

            $validated['user_id'] = Auth::id();

            $card = Card::create($validated);

            return new JsonResource($card);
        } catch (Exception $e) {
            return JsonResponse::respondError($e->getMessage());
        }
    }
}
