<?php

namespace App\Infrastructure\Rdash\Repositories;

use App\Domain\Rdash\ObjectStorage\Contracts\ObjectStorageRepository as ObjectStorageRepositoryContract;
use App\Domain\Rdash\ObjectStorage\ValueObjects\AccessKey;
use App\Domain\Rdash\ObjectStorage\ValueObjects\Bucket;
use App\Domain\Rdash\ObjectStorage\ValueObjects\ObjectStorage;
use App\Infrastructure\Rdash\HttpClient;

class ObjectStorageRepository implements ObjectStorageRepositoryContract
{
    public function __construct(
        private HttpClient $client
    ) {
    }

    public function getAll(array $filters = []): array
    {
        $data = $this->client->get('/object-storage', $filters);
        $storages = $data['data'] ?? [];

        return array_map(
            fn (array $storage) => ObjectStorage::fromArray($storage),
            $storages
        );
    }

    public function getById(int $objectStorageId): ?ObjectStorage
    {
        $data = $this->client->get("/object-storage/{$objectStorageId}");

        return empty($data) ? null : ObjectStorage::fromArray($data);
    }

    public function buy(array $data): ObjectStorage
    {
        $response = $this->client->post('/object-storage', $data);

        return ObjectStorage::fromArray($response);
    }

    public function renew(int $objectStorageId): bool
    {
        $this->client->post("/object-storage/{$objectStorageId}/renew");

        return true;
    }

    public function upgrade(int $objectStorageId, int $newSize): bool
    {
        $this->client->put("/object-storage/{$objectStorageId}/upgrade", [
            'size' => $newSize,
        ]);

        return true;
    }

    public function suspend(int $objectStorageId, string $reason): bool
    {
        $this->client->put("/object-storage/{$objectStorageId}/suspended", [
            'reason' => $reason,
        ]);

        return true;
    }

    public function unsuspend(int $objectStorageId): bool
    {
        $this->client->delete("/object-storage/{$objectStorageId}/suspended");

        return true;
    }

    public function delete(int $objectStorageId): bool
    {
        $this->client->delete("/object-storage/{$objectStorageId}");

        return true;
    }

    public function getBuckets(int $objectStorageId): array
    {
        $data = $this->client->get("/object-storage/{$objectStorageId}/buckets");
        $buckets = $data['buckets'] ?? [];

        return array_map(
            fn (array $bucket) => Bucket::fromArray($bucket),
            $buckets
        );
    }

    public function createBucket(int $objectStorageId, string $bucketName): Bucket
    {
        $response = $this->client->post("/object-storage/{$objectStorageId}/buckets", [
            'name' => $bucketName,
        ]);

        return Bucket::fromArray($response);
    }

    public function deleteBucket(int $objectStorageId, string $bucketName): bool
    {
        $this->client->delete("/object-storage/{$objectStorageId}/buckets/{$bucketName}");

        return true;
    }

    public function getAccessKeys(int $objectStorageId): array
    {
        $data = $this->client->get("/object-storage/{$objectStorageId}/keys");
        $keys = $data['keys'] ?? [];

        return array_map(
            fn (array $key) => AccessKey::fromArray($key),
            $keys
        );
    }

    public function createAccessKey(int $objectStorageId, array $data): AccessKey
    {
        $response = $this->client->post("/object-storage/{$objectStorageId}/keys", $data);

        return AccessKey::fromArray($response);
    }

    public function deleteAccessKey(int $objectStorageId, int $keyId): bool
    {
        $this->client->delete("/object-storage/{$objectStorageId}/keys/{$keyId}");

        return true;
    }
}

