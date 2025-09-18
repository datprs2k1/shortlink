<?php

namespace App\Repositories\Click;

use App\Models\Click;
use App\Repositories\_Abstract\BaseRepository;
use App\Repositories\Click\IClickRepository;

class ClickRepository extends BaseRepository implements IClickRepository
{
    public function model(): string
    {
        return Click::class;
    }
}