<?php

namespace App\Repositories\Domain;

use App\Models\Domain;
use App\Repositories\_Abstract\BaseRepository;
use App\Repositories\Domain\IDomainRepository;

class DomainRepository extends BaseRepository implements IDomainRepository
{
    public function model(): string
    {
        return Domain::class;
    }
}
