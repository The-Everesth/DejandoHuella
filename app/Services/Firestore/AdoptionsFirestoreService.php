<?php

namespace App\Services\Firestore;

use Illuminate\Support\Str;

class AdoptionsFirestoreService
{
    protected FirestoreRestClient $client;
    protected string $collection = 'adopciones';
    protected array $readCollections = ['adopciones', 'adoptionPosts'];

    public function __construct(FirestoreRestClient $client)
    {
        $this->client = $client;
    }

    public function get(string $id): ?array
    {
        foreach ($this->readCollections as $collection) {
            $doc = $this->client->getDoc($collection, $id);
            if (is_array($doc)) {
                return $this->normalizeAdoption($doc, $id);
            }
        }

        return null;
    }

    public function list(): array
    {
        $merged = [];

        foreach ($this->readCollections as $collection) {
            foreach ($this->client->listDocs($collection) as $docId => $doc) {
                if (! is_array($doc)) {
                    continue;
                }

                $normalized = $this->normalizeAdoption($doc, (string) $docId);
                $id = (string) ($normalized['id'] ?? $docId);
                if ($id === '') {
                    continue;
                }

                $merged[$id] = $normalized;
            }
        }

        return $merged;
    }

    public function create(array $data, ?string $id = null): array
    {
        return $this->client->createDoc($this->collection, $id, $data);
    }

    public function update(string $id, array $data): bool
    {
        $targetCollection = $this->resolveCollectionForId($id);
        return $this->client->patchDoc($targetCollection, $id, $data);
    }

    public function delete(string $id): bool
    {
        $targetCollection = $this->resolveCollectionForId($id);
        return $this->client->deleteDoc($targetCollection, $id);
    }

    protected function resolveCollectionForId(string $id): string
    {
        foreach ($this->readCollections as $collection) {
            if ($this->client->getDoc($collection, $id)) {
                return $collection;
            }
        }

        return $this->collection;
    }

    protected function normalizeAdoption(array $doc, string $fallbackId): array
    {
        $id = (string) ($doc['id'] ?? $doc['_docId'] ?? $fallbackId);
        $createdBy = $doc['createdBy']
            ?? $doc['created_by']
            ?? $doc['publisherId']
            ?? $doc['userId']
            ?? null;

        $normalized = $doc;
        $normalized['id'] = $id;
        $normalized['nombreAnimal'] = $doc['nombreAnimal'] ?? $doc['petName'] ?? $doc['title'] ?? 'Mascota sin nombre';
        $normalized['tipoAnimal'] = $doc['tipoAnimal'] ?? $doc['petType'] ?? 'No especificado';
        $normalized['sexo'] = $doc['sexo'] ?? $doc['sex'] ?? $doc['gender'] ?? '';
        $normalized['edad'] = $doc['edad'] ?? $doc['age'] ?? null;
        $normalized['raza'] = $doc['raza'] ?? $doc['breed'] ?? 'No especificada';
        $normalized['detalles'] = $doc['detalles'] ?? $doc['description'] ?? '';
        $normalized['fecha'] = $doc['fecha'] ?? $doc['createdAt'] ?? $doc['created_at'] ?? now()->toIso8601String();
        $normalized['createdBy'] = $createdBy;
        $normalized['isHidden'] = $doc['isHidden'] ?? $doc['hidden'] ?? false;

        $normalized['imagePath'] = $this->resolveImagePath($doc);
        $normalized['imageUrl'] = $this->resolveImageUrl($doc, $normalized['imagePath']);

        return $normalized;
    }

    protected function resolveImagePath(array $doc): ?string
    {
        $candidates = [
            'imagePath',
            'image_path',
            'photoPath',
            'photo_path',
            'fotoPath',
            'foto_path',
        ];

        foreach ($candidates as $key) {
            $value = trim((string) ($doc[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }

    protected function resolveImageUrl(array $doc, ?string $imagePath): ?string
    {
        $urlCandidates = [
            'imageUrl',
            'image_url',
            'photoUrl',
            'photo_url',
            'fotoUrl',
            'foto_url',
            'fotoMascota',
        ];

        foreach ($urlCandidates as $key) {
            $value = trim((string) ($doc[$key] ?? ''));
            if ($value === '') {
                continue;
            }

            if (Str::startsWith($value, ['http://', 'https://', 'data:image/'])) {
                $parsedPath = parse_url($value, PHP_URL_PATH);
                if (is_string($parsedPath) && str_contains($parsedPath, '/uploads/')) {
                    $relative = ltrim($parsedPath, '/');
                    return url('/'.$relative);
                }

                return $value;
            }

            // It's a relative path, build public URL
            return $this->buildPublicUrlFromPath($value);
        }

        if (! empty($imagePath)) {
            return $this->buildPublicUrlFromPath($imagePath);
        }

        return null;
    }

    protected function buildPublicUrlFromPath(string $path): string
    {
        // Handle: normalize path separators
        $normalizedPath = str_replace('\\', '/', trim($path));
        
        // If path already references uploads, use it as-is
        if (str_contains($normalizedPath, 'uploads/')) {
            $normalizedPath = substr($normalizedPath, strpos($normalizedPath, 'uploads/'));
        } else if (str_contains($normalizedPath, 'public/uploads/')) {
            // If it includes public/, strip it since we'll add URL root
            $normalizedPath = substr($normalizedPath, strpos($normalizedPath, 'public/uploads/') + strlen('public/'));
        } else {
            // Assume it's just a filename or relative path without uploads/ prefix
            // Add uploads/adoptions/ prefix for adoption images
            if (!str_contains($normalizedPath, '/')) {
                $normalizedPath = 'uploads/adoptions/' . $normalizedPath;
            }
        }

        return url('/'.ltrim($normalizedPath, '/'));
    }
}
