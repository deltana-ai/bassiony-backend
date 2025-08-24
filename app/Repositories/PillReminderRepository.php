<?php

namespace App\Repositories;

use App\Interfaces\PillReminderRepositoryInterface;
use App\Models\PillReminder;

class PillReminderRepository implements PillReminderRepositoryInterface
{
    public function all()
    {
        return PillReminder::all();
    }

    public function findById(int $id)
    {
        return PillReminder::findOrFail($id);
    }

    public function create(array $data)
    {
        return PillReminder::create($data);
    }

    public function update(int $id, array $data)
    {
        $pillReminder = $this->findById($id);
        $pillReminder->update($data);
        return $pillReminder;
    }

    public function delete(int $id)
    {
        $pillReminder = $this->findById($id);
        return $pillReminder->delete();
    }
}