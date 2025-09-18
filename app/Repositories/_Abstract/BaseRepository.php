<?php

namespace App\Repositories\_Abstract;

use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Eloquent\BaseRepository as BRepository;

/**
 * Class BaseRepository
 *
 * @package App\Entities\Admin\Repositories
 */
abstract class BaseRepository extends BRepository
{


    protected $filters = [];

    /**
     * @return Builder
     */
    public function getQuery(): Builder
    {
        return $this->getModel()->newQuery();
    }

    public function findById($id)
    {
        return $this->findWhere(['id' => $id])->first();
    }
}
