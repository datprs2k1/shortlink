<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\_Abstract\BaseRepository;
use App\Repositories\User\IUserRepository;

class UserRepository extends BaseRepository implements IUserRepository
{
    public function model(): string
    {
        return User::class;
    }
}