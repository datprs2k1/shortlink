<?php

namespace App\Repositories\Shortlink;

use App\Models\Shortlink;
use App\Repositories\_Abstract\BaseRepository;
use App\Repositories\Shortlink\IShortlinkRepository;

class ShortlinkRepository extends BaseRepository implements IShortlinkRepository
{
    public function model(): string
    {
        return Shortlink::class;
    }
}