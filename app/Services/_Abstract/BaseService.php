<?php

namespace App\Services\_Abstract;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    /**
     * The main repository instance
     */
    public $mainRepository;

    /**
     * Get all records
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->mainRepository->all();
    }

    /**
     * Find record by ID
     *
     * @param int $id
     * @return Model|null
     */
    public function findById(int $id): ?Model
    {
        return $this->mainRepository->findById($id);
    }

    /**
     * Create a new record
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->mainRepository->create($data);
    }

    /**
     * Update a record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->mainRepository->update($data, $id);
    }

    /**
     * Delete a record
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        return $this->mainRepository->delete($id);
    }

    /**
     * Find records by criteria
     *
     * @param array $criteria
     * @return Collection
     */
    public function findWhere(array $criteria): Collection
    {
        return $this->mainRepository->findWhere($criteria);
    }
}
