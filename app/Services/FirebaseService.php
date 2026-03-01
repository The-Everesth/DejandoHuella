<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FirebaseService
{
    protected $httpClient;
    protected $credentials;
    protected $projectId;
    protected $adoptionsReference = 'adopciones';
    protected $firestoreBaseUrl = 'https://firestore.googleapis.com/v1/projects/%s/databases/(default)/documents';

    public function __construct()
    {
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS_PATH'));

        if (!file_exists($credentialsPath)) {
            throw new \RuntimeException('No se encontró el archivo de credenciales de Firebase.');
        }

        $credentialsJson = json_decode(file_get_contents($credentialsPath), true);

        if (!is_array($credentialsJson) || empty($credentialsJson['project_id'])) {
            throw new \RuntimeException('El archivo de credenciales de Firebase no es válido.');
        }

        $this->projectId = $credentialsJson['project_id'];
        $this->credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/datastore'],
            $credentialsJson
        );

        $this->httpClient = new Client([
            'timeout' => 15,
        ]);
    }

    protected function firestoreCollectionUrl()
    {
        return sprintf($this->firestoreBaseUrl, $this->projectId) . '/' . $this->adoptionsReference;
    }

    protected function firestoreDocumentUrl($id)
    {
        return $this->firestoreCollectionUrl() . '/' . $id;
    }

    protected function getAccessToken()
    {
        $tokenData = $this->credentials->fetchAuthToken();

        if (!is_array($tokenData) || empty($tokenData['access_token'])) {
            throw new \RuntimeException('No se pudo obtener token de acceso para Firestore.');
        }

        return $tokenData['access_token'];
    }

    protected function encodeFirestoreValue($value)
    {
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

        if (is_array($value)) {
            $isList = array_keys($value) === range(0, count($value) - 1);

            if ($isList) {
                $arrayValues = [];

                foreach ($value as $item) {
                    $arrayValues[] = $this->encodeFirestoreValue($item);
                }

                return ['arrayValue' => ['values' => $arrayValues]];
            }

            $mapFields = [];

            foreach ($value as $key => $item) {
                $mapFields[$key] = $this->encodeFirestoreValue($item);
            }

            return ['mapValue' => ['fields' => $mapFields]];
        }

        return ['stringValue' => (string) $value];
    }

    protected function decodeFirestoreValue(array $value)
    {
        if (array_key_exists('nullValue', $value)) {
            return null;
        }

        if (array_key_exists('booleanValue', $value)) {
            return (bool) $value['booleanValue'];
        }

        if (array_key_exists('integerValue', $value)) {
            return (int) $value['integerValue'];
        }

        if (array_key_exists('doubleValue', $value)) {
            return (float) $value['doubleValue'];
        }

        if (array_key_exists('stringValue', $value)) {
            return (string) $value['stringValue'];
        }

        if (array_key_exists('timestampValue', $value)) {
            return (string) $value['timestampValue'];
        }

        if (array_key_exists('mapValue', $value)) {
            $decoded = [];
            $fields = $value['mapValue']['fields'] ?? [];

            foreach ($fields as $key => $fieldValue) {
                $decoded[$key] = $this->decodeFirestoreValue($fieldValue);
            }

            return $decoded;
        }

        if (array_key_exists('arrayValue', $value)) {
            $decoded = [];
            $values = $value['arrayValue']['values'] ?? [];

            foreach ($values as $item) {
                $decoded[] = $this->decodeFirestoreValue($item);
            }

            return $decoded;
        }

        return null;
    }

    protected function toFirestoreFields(array $data)
    {
        $fields = [];

        foreach ($data as $key => $value) {
            $fields[$key] = $this->encodeFirestoreValue($value);
        }

        return $fields;
    }

    protected function fromFirestoreDocument(array $document)
    {
        $fields = $document['fields'] ?? [];
        $decoded = [];

        foreach ($fields as $key => $value) {
            $decoded[$key] = $this->decodeFirestoreValue($value);
        }

        if (!isset($decoded['id']) || empty($decoded['id'])) {
            $name = $document['name'] ?? '';
            $decoded['id'] = basename($name);
        }

        return $decoded;
    }

    protected function requestFirestore($method, $url, array $options = [])
    {
        $token = $this->getAccessToken();

        $defaultOptions = [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ];

        if (isset($options['json'])) {
            $defaultOptions['headers']['Content-Type'] = 'application/json';
        }

        $requestOptions = array_merge($defaultOptions, $options);

        return $this->httpClient->request($method, $url, $requestOptions);
    }

    /**
     * Guardar una adopción en Firebase
     */
    public function saveAdoption(array $data)
    {
        try {
            if (!isset($data['id']) || empty($data['id'])) {
                throw new \InvalidArgumentException('El campo id es requerido para guardar una adopción.');
            }

            $this->requestFirestore('PATCH', $this->firestoreDocumentUrl($data['id']), [
                'json' => [
                    'fields' => $this->toFirestoreFields($data),
                ],
            ]);
            
            return [
                'success' => true,
                'message' => 'Adopción registrada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al guardar adopción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener todas las adopciones
     */
    public function getAllAdoptions()
    {
        try {
            $response = $this->requestFirestore('GET', $this->firestoreCollectionUrl());
            $payload = json_decode((string) $response->getBody(), true);
            $documents = $payload['documents'] ?? [];

            $adoptions = [];

            foreach ($documents as $document) {
                $data = $this->fromFirestoreDocument($document);
                $adoptions[$data['id']] = $data;
            }

            return $adoptions;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener una adopción por ID
     */
    public function getAdoptionById(string $id)
    {
        try {
            $response = $this->requestFirestore('GET', $this->firestoreDocumentUrl($id));
            $payload = json_decode((string) $response->getBody(), true);

            if (!is_array($payload) || empty($payload['name'])) {
                return null;
            }

            return $this->fromFirestoreDocument($payload);
        } catch (RequestException $e) {
            $status = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;

            if ($status === 404) {
                return null;
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Actualizar una adopción
     */
    public function updateAdoption(string $id, array $data)
    {
        try {
            if ($this->getAdoptionById($id) === null) {
                return [
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ];
            }

            $queryParams = [];

            foreach (array_keys($data) as $field) {
                $queryParams[] = 'updateMask.fieldPaths=' . rawurlencode($field);
            }

            $url = $this->firestoreDocumentUrl($id);

            if (!empty($queryParams)) {
                $url .= '?' . implode('&', $queryParams);
            }

            $this->requestFirestore('PATCH', $url, [
                'json' => [
                    'fields' => $this->toFirestoreFields($data),
                ],
            ]);
            
            return [
                'success' => true,
                'message' => 'Adopción actualizada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar adopción: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar una adopción
     */
    public function deleteAdoption(string $id)
    {
        try {
            if ($this->getAdoptionById($id) === null) {
                return [
                    'success' => false,
                    'message' => 'Adopción no encontrada'
                ];
            }

            $this->requestFirestore('DELETE', $this->firestoreDocumentUrl($id));
            
            return [
                'success' => true,
                'message' => 'Adopción eliminada correctamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar adopción: ' . $e->getMessage()
            ];
        }
    }
}
