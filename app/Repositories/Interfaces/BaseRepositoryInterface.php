<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     */
    public function all(): Collection;

    /**
     * Get paginated records
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Find record by ID
     */
    public function find(int $id): ?Model;

    /**
     * Find record by ID or fail
     */
    public function findOrFail(int $id): Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update record by ID
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete record by ID
     */
    public function delete(int $id): bool;

    /**
     * Find records by column value
     */
    public function findBy(string $column, $value): Collection;

    /**
     * Find first record by column value
     */
    public function findOneBy(string $column, $value): ?Model;

    /**
     * Get records with conditions
     */
    public function where(array $conditions): Collection;

    /**
     * Get records with ordering
     */
    public function orderBy(string $column, string $direction = 'asc'): Collection;

    /**
     * Get records count
     */
    public function count(): int;

    /**
     * Check if record exists
     */
    public function exists(int $id): bool;

    /**
     * Get first record
     */
    public function first(): ?Model;

    /**
     * Get latest records
     */
    public function latest(string $column = 'created_at'): Collection;

    /**
     * Get oldest records
     */
    public function oldest(string $column = 'created_at'): Collection;
}
