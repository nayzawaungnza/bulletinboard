<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Find record by ID
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find record by ID or fail
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update record by ID
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->find($id);
        if (!$record) {
            return false;
        }
        return $record->update($data);
    }

    /**
     * Delete record by ID
     */
    public function delete(int $id): bool
    {
        $record = $this->find($id);
        if (!$record) {
            return false;
        }
        return $record->delete();
    }

    /**
     * Find records by column value
     */
    public function findBy(string $column, $value): Collection
    {
        return $this->model->where($column, $value)->get();
    }

    /**
     * Find first record by column value
     */
    public function findOneBy(string $column, $value): ?Model
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * Get records with conditions
     */
    public function where(array $conditions): Collection
    {
        $query = $this->model->newQuery();
        
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }
        
        return $query->get();
    }

    /**
     * Get records with ordering
     */
    public function orderBy(string $column, string $direction = 'asc'): Collection
    {
        return $this->model->orderBy($column, $direction)->get();
    }

    /**
     * Get records count
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Check if record exists
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    /**
     * Get first record
     */
    public function first(): ?Model
    {
        return $this->model->first();
    }

    /**
     * Get latest records
     */
    public function latest(string $column = 'created_at'): Collection
    {
        return $this->model->latest($column)->get();
    }

    /**
     * Get oldest records
     */
    public function oldest(string $column = 'created_at'): Collection
    {
        return $this->model->oldest($column)->get();
    }
}
