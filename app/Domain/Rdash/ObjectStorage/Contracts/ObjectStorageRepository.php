<?php

namespace App\Domain\Rdash\ObjectStorage\Contracts;

use App\Domain\Rdash\ObjectStorage\ValueObjects\ObjectStorage;
use App\Domain\Rdash\ObjectStorage\ValueObjects\Bucket;
use App\Domain\Rdash\ObjectStorage\ValueObjects\AccessKey;

interface ObjectStorageRepository
{
    /**
     * Get list all object storage
     *
     * @param array<string, mixed> $filters
     * @return array<int, ObjectStorage>
     */
    public function getAll(array $filters = []): array;

    /**
     * Get object storage by id
     */
    public function getById(int $objectStorageId): ?ObjectStorage;

    /**
     * Buy object storage
     *
     * @param array<string, mixed> $data
     */
    public function buy(array $data): ObjectStorage;

    /**
     * Renew object storage
     */
    public function renew(int $objectStorageId): bool;

    /**
     * Upgrade object storage
     */
    public function upgrade(int $objectStorageId, int $newSize): bool;

    /**
     * Suspend object storage
     */
    public function suspend(int $objectStorageId, string $reason): bool;

    /**
     * Unsuspend object storage
     */
    public function unsuspend(int $objectStorageId): bool;

    /**
     * Delete object storage
     */
    public function delete(int $objectStorageId): bool;

    /**
     * Get buckets
     *
     * @return array<int, Bucket>
     */
    public function getBuckets(int $objectStorageId): array;

    /**
     * Create bucket
     */
    public function createBucket(int $objectStorageId, string $bucketName): Bucket;

    /**
     * Delete bucket
     */
    public function deleteBucket(int $objectStorageId, string $bucketName): bool;

    /**
     * Get access keys
     *
     * @return array<int, AccessKey>
     */
    public function getAccessKeys(int $objectStorageId): array;

    /**
     * Create access key
     *
     * @param array<string, mixed> $data
     */
    public function createAccessKey(int $objectStorageId, array $data): AccessKey;

    /**
     * Delete access key
     */
    public function deleteAccessKey(int $objectStorageId, int $keyId): bool;
}

