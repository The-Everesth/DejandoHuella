<?php

namespace App\Services\Firestore;

use GuzzleHttp\Client as GuzzleClient;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Log;

class FirestoreRestClient
{
    protected string $projectId;
    protected string $serviceAccountPath;
    protected GuzzleClient $http;
    protected ?array $token = null;

    public function __construct()
    {
        // Determine credentials path first (required to read project id when missing)
        $path = config('firebase.credentials') ?: base_path(env('FIREBASE_CREDENTIALS'));
        if (! file_exists($path)) {
            throw new \RuntimeException("No se encontró el archivo de credenciales de Firebase: $path");
        }

        // project id can come from config/env or inside the credentials json
        $proj = config('firebase.project_id') ?: env('FIREBASE_PROJECT_ID');
        if (empty($proj)) {
            $json = json_decode(file_get_contents($path), true);
            $proj = is_array($json) && ! empty($json['project_id']) ? $json['project_id'] : '';
        }

        if (empty($proj)) {
            throw new \RuntimeException('project_id de Firebase no configurado');
        }

        // ensure project id matches credentials file
        $jsonForCheck = json_decode(file_get_contents($path), true);
        if (is_array($jsonForCheck) && ! empty($jsonForCheck['project_id']) && $jsonForCheck['project_id'] !== $proj) {
            Log::warning('Project ID mismatch between config/env and service account JSON', [
                'env' => $proj,
                'json' => $jsonForCheck['project_id'],
            ]);
        }

        $this->projectId = $proj;
        $this->serviceAccountPath = $path;
        // configure client defaults so later methods don't need to repeat
        $this->http = new GuzzleClient([
            'timeout' => 15,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Get a valid access token for Firestore REST API.
     * Uses ServiceAccountCredentials with proper handler stack.
     */
    protected function getAccessToken(): string
    {
        // cached token avoids extra network calls
        if ($this->token && isset($this->token['expires_at']) && $this->token['expires_at'] > time() + 30) {
            return $this->token['access_token'];
        }

        try {
            $json = json_decode(file_get_contents($this->serviceAccountPath), true);
            if (! is_array($json)) {
                throw new \RuntimeException('Invalid service account JSON: '.$this->serviceAccountPath);
            }

            $scopes = ['https://www.googleapis.com/auth/datastore'];
            $creds = new ServiceAccountCredentials($scopes, $json);
            // use our configured client for the HTTP handler to inherit timeouts
            $httpHandler = \Google\Auth\HttpHandler\HttpHandlerFactory::build($this->http);

            $authToken = $creds->fetchAuthToken($httpHandler);
            if (! isset($authToken['access_token'])) {
                throw new \RuntimeException('Unable to fetch access token from service account.');
            }

            $this->token = [
                'access_token' => $authToken['access_token'],
                'expires_at' => time() + ($authToken['expires_in'] ?? 3600),
            ];

            return $this->token['access_token'];
        } catch (\Throwable $e) {
            Log::error('Error obteniendo token de Firebase', ['exception' => $e]);
            throw $e;
        }
    }

    protected function baseUrl(): string
    {
        return "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents";
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->getAccessToken(),
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Convert a PHP array to Firestore fields recursively (maps/lists).
     */
    protected function phpToFields(array $data): array
    {
        $encode = function ($value) use (&$encode) {
            if (is_null($value)) {
                return ['nullValue' => null];
            }
            if (is_bool($value)) {
                return ['booleanValue' => $value];
            }
            if (is_int($value)) {
                return ['integerValue' => (string) $value];
            }
            if (is_float($value)) {
                return ['doubleValue' => $value];
            }
            if ($value instanceof \DateTimeInterface) {
                return ['timestampValue' => $value->format('Y-m-d\TH:i:s.u\Z')];
            }
            if (is_array($value)) {
                $isList = array_keys($value) === range(0, count($value) - 1);
                if ($isList) {
                    $vals = [];
                    foreach ($value as $item) {
                        $vals[] = $encode($item);
                    }
                    return ['arrayValue' => ['values' => $vals]];
                }
                $map = [];
                foreach ($value as $k => $v) {
                    $map[$k] = $encode($v);
                }
                return ['mapValue' => ['fields' => $map]];
            }
            return ['stringValue' => (string) $value];
        };

        $fields = [];
        foreach ($data as $k => $v) {
            $fields[$k] = $encode($v);
        }

        return $fields;
    }

    public function getDocument(string $documentPath): ?array
    {
        $url = $this->baseUrl().'/'.rawurlencode($documentPath);
        try {
            $body = $this->request('GET', $url);
            return $body;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                return null;
            }
            throw $e;
        }
    }

    public function listDocuments(string $collectionPath, array $queryParams = []): array
    {
        $url = $this->baseUrl().'/'.rawurlencode($collectionPath);
        if (! empty($queryParams)) {
            $url .= '?'.http_build_query($queryParams);
        }
        $res = $this->request('GET', $url);
        // ensure structure
        return is_array($res) ? $res : [];
    }

    public function createDocument(string $collectionPath, string $documentId, array $data): array
    {
        $url = $this->baseUrl().'/'.rawurlencode($collectionPath)."?documentId=".rawurlencode($documentId);
        $body = ['fields' => $this->phpToFields($data)];
        return $this->request('POST', $url, ['json' => $body]);
    }

    public function patchDocument(string $documentPath, array $data, array $updateMaskFields = []): array
    {
        $url = $this->baseUrl().'/'.rawurlencode($documentPath);
        $body = ['fields' => $this->phpToFields($data)];
        if (! empty($updateMaskFields)) {
            $body['updateMask'] = ['fieldPaths' => $updateMaskFields];
        }
        return $this->request('PATCH', $url, ['json' => $body]);
    }

    public function deleteDocument(string $documentPath): bool
    {
        $url = $this->baseUrl().'/'.rawurlencode($documentPath);
        $this->request('DELETE', $url);
        return true; // success if no exception
    }

    /* --------------------------------------------------------------------- */
    /*  helper de petición con logging y timeouts                           */
    /* --------------------------------------------------------------------- */

    /**
     * Ejecuta una petición HTTP hacia Firestore y devuelve el cuerpo descodificado.
     * Lanza excepciones en caso de error y las registra.
     */
    protected function request(string $method, string $url, array $options = [])
    {
        $opts = array_merge_recursive([
            'headers' => $this->headers(),
            // timeouts también se pueden configurar en constructor, pero repetimos
            'timeout' => 15,
            'connect_timeout' => 10,
        ], $options);

        try {
            $res = $this->http->request($method, $url, $opts);
            $status = $res->getStatusCode();
            $body = json_decode((string) $res->getBody(), true);
            if ($status < 200 || $status >= 300) {
                Log::error('Firestore response non-2xx', ['method' => $method, 'url' => $url, 'status' => $status, 'body' => $body]);
            }
            return $body;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $resp = $e->getResponse();
            $status = $resp ? $resp->getStatusCode() : null;
            $body = $resp ? (string)$resp->getBody() : null;
            Log::error('Firestore request failed', ['method' => $method, 'url' => $url, 'status' => $status, 'body' => $body, 'exception' => $e]);
            throw $e;
        }
    }

    /* --------------------------------------------------------------------- */

    protected function fromFirestoreDocument(array $document): array
    {
        $decoded = [];
        $fields = $document['fields'] ?? [];
        $decode = function ($value) use (&$decode) {
            if (array_key_exists('nullValue', $value)) {
                return null;
            }
            if (array_key_exists('booleanValue', $value)) {
                return (bool)$value['booleanValue'];
            }
            if (array_key_exists('integerValue', $value)) {
                return (int)$value['integerValue'];
            }
            if (array_key_exists('doubleValue', $value)) {
                return (float)$value['doubleValue'];
            }
            if (array_key_exists('stringValue', $value)) {
                return (string)$value['stringValue'];
            }
            if (array_key_exists('timestampValue', $value)) {
                return (string)$value['timestampValue'];
            }
            if (array_key_exists('mapValue', $value)) {
                $out = [];
                foreach ($value['mapValue']['fields'] ?? [] as $k => $v) {
                    $out[$k] = $decode($v);
                }
                return $out;
            }
            if (array_key_exists('arrayValue', $value)) {
                $out = [];
                foreach ($value['arrayValue']['values'] ?? [] as $item) {
                    $out[] = $decode($item);
                }
                return $out;
            }
            return null;
        };

        foreach ($fields as $k => $v) {
            $decoded[$k] = $decode($v);
        }

        if (!isset($decoded['id']) || empty($decoded['id'])) {
            $name = $document['name'] ?? '';
            $decoded['id'] = basename($name);
        }

        return $decoded;
    }

    public function getDoc(string $collection, string $id): ?array
    {
        $doc = $this->getDocument("{$collection}/{$id}");
        return $doc ? $this->fromFirestoreDocument($doc) : null;
    }

    public function listDocs(string $collection): array
    {
        $res = $this->listDocuments($collection);
        $docs = $res['documents'] ?? [];
        $out = [];
        foreach ($docs as $d) {
            $decoded = $this->fromFirestoreDocument($d);
            $out[$decoded['id']] = $decoded;
        }
        return $out;
    }

    public function createDoc(string $collection, ?string $id, array $data): array
    {
        if ($id) {
            // upsert: patchDoc checks existence and crea si falta
            $this->patchDoc($collection, $id, $data);
            return $this->getDoc($collection, $id) ?? [];
        }

        $url = $this->baseUrl().'/'.rawurlencode($collection);
        $body = ['fields' => $this->phpToFields($data)];
        $payload = $this->request('POST', $url, ['json' => $body]);
        return $this->fromFirestoreDocument($payload);
    }

    public function patchDoc(string $collection, string $id, array $data): bool
    {
        $existing = $this->getDoc($collection, $id);
        if (is_null($existing)) {
            // crear documento nuevo con id
            $this->createDocument($collection, $id, $data);
            return true;
        }
        $fields = array_keys($data);
        $this->patchDocument("{$collection}/{$id}", $data, $fields);
        return true;
    }

    public function deleteDoc(string $collection, string $id): bool
    {
        return $this->deleteDocument("{$collection}/{$id}");
    }
}
