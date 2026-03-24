<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use GuzzleHttp\Client;
use Google\Auth\Credentials\ServiceAccountCredentials;


class FirebaseStorageService
{
    protected $bucket;
    protected $projectId;
    protected $credentials;
    protected $httpClient;

    public function __construct()
    {
        $credentialsPath = base_path(env('FIREBASE_CREDENTIALS_PATH'));
        $credentialsJson = json_decode(file_get_contents($credentialsPath), true);
        $this->projectId = $credentialsJson['project_id'];
        $this->bucket = $credentialsJson['storage_bucket'] ?? (env('FIREBASE_STORAGE_BUCKET') ?: $this->projectId . '.appspot.com');
        $this->credentials = new ServiceAccountCredentials([
            'https://www.googleapis.com/auth/devstorage.read_write',
        ], $credentialsJson);
        $this->httpClient = new Client(['timeout' => 30]);
    }



    public function uploadPetPhoto(UploadedFile $file, string $ownerUid, string $petId): array
    {
        try {
            $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = 'profile.' . $ext;
            $storagePath = "pets/{$ownerUid}/{$petId}/{$filename}";
            $bucket = $this->bucket;

            $tokenData = $this->credentials->fetchAuthToken();
            if (empty($tokenData['access_token'])) {
                throw new \Exception('No se pudo obtener access_token para Firebase Storage.');
            }
            $accessToken = $tokenData['access_token'];
            $url = "https://storage.googleapis.com/upload/storage/v1/b/{$bucket}/o?uploadType=media&name=" . urlencode($storagePath);

            Log::info('[PET PHOTO] Subiendo archivo', [
                'bucket' => $bucket,
                'storagePath' => $storagePath,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'url' => $url,
            ]);

            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => $file->getMimeType(),
                ],
                'body' => file_get_contents($file->getPathname()),
            ]);

            $statusCode = $response->getStatusCode();
            $rawBody = (string) $response->getBody();
            $result = json_decode($rawBody, true);

            Log::info('[PET PHOTO] Respuesta de subida', [
                'status' => $statusCode,
                'body' => $result,
            ]);

            if ($statusCode < 200 || $statusCode >= 300) {
                throw new \Exception('Firebase Storage respondió con código ' . $statusCode);
            }
            if (empty($result['name'])) {
                throw new \Exception('La respuesta de Storage no contiene el nombre del archivo subido.');
            }

            $publicUrl = "https://storage.googleapis.com/{$bucket}/" . str_replace('%2F', '/', rawurlencode($storagePath));
            return [
                'photoUrl' => $publicUrl,
                'photoPath' => $storagePath,
            ];
        } catch (\Throwable $e) {
            Log::error('[PET PHOTO] Falló la subida', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw new \Exception('No se pudo subir la foto a Firebase Storage: ' . $e->getMessage(), 0, $e);
        }
    }
}
