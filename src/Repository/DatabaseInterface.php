<?php

namespace App\Repository;

use App\Entity\DatabaseException;
use App\Entity\EntityInterface;

/**
 * Interface DatabaseInterface.
 */
interface DatabaseInterface
{
    /**
     * @return array|null
     */
    public function getAllRecords(): ?array;

    /**
     * @param string $id
     */
    public function getRecord(string $id): ?EntityInterface;

    /**
     * @return mixed
     */
    public function saveRecord(EntityInterface $entity);

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function deleteRecord(string $id): bool;

    /**
     * @param string $id
     *
     * @return mixed
     */
    public function updateRecord(string $id, $entity): void;

    /**
     * @return string
     */
    public function getEntityClass(): string;

    /**
     * @return array
     */
    public function getEntityGetters(): array;

    /**
     * @return array
     */
    public function getEntitySetters(): array;
}
