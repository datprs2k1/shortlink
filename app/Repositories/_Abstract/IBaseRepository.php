<?php

namespace App\Repositories\_Abstract;

use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\RepositoryInterface;

interface IBaseRepository extends RepositoryInterface
{
    public function getQuery(): Builder;
    public function model();

    public function findById($id);
}
