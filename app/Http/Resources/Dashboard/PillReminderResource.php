<?php

namespace App\Http\Resources\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PillReminderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'dosage' => $this->dosage,
            'notes' => $this->notes,
            'time' => $this->time->format('H:i'),
            'repeat' => $this->repeat,
            'days' => $this->days,
            'user' => $this->user,
        ];
    }
}