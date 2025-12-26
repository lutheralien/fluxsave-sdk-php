<?php

namespace Fluxsave;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class FluxsaveError extends \Exception
{
    public mixed $data;

    public function __construct(string $message, int $code, mixed $data = null)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }
}

class FluxsaveClient
{
    private string $baseUrl;
    private ?string $apiKey;
    private ?string $apiSecret;
    private Client $client;

    public function __construct(string $baseUrl, ?string $apiKey = null, ?string $apiSecret = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->client = new Client();
    }

    public function setAuth(string $apiKey, string $apiSecret): void
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    private function headers(): array
    {
        if (!$this->apiKey || !$this->apiSecret) {
            throw new FluxsaveError('API key and secret are required', 401);
        }

        return [
            'x-api-key' => $this->apiKey,
            'x-api-secret' => $this->apiSecret,
        ];
    }

    private function request(string $method, string $path, array $options = []): array
    {
        $options['headers'] = array_merge($this->headers(), $options['headers'] ?? []);
        try {
            $response = $this->client->request($method, $this->baseUrl . $path, $options);
            $body = (string) $response->getBody();
            $data = json_decode($body, true);
            return $data ?? ['status' => $response->getStatusCode(), 'message' => $body];
        } catch (GuzzleException $e) {
            $message = $e->getMessage();
            $code = $e->getCode() ?: 500;
            throw new FluxsaveError($message, (int) $code);
        }
    }

    public function uploadFile(string $filePath, ?string $name = null, ?bool $transform = null): array
    {
        $multipart = [
            [
                'name' => 'file',
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath),
            ],
        ];

        if ($name) {
            $multipart[] = ['name' => 'name', 'contents' => $name];
        }
        if ($transform !== null) {
            $multipart[] = ['name' => 'transform', 'contents' => $transform ? 'true' : 'false'];
        }

        return $this->request('POST', '/api/v1/files/upload', ['multipart' => $multipart]);
    }

    public function uploadFiles(array $filePaths, ?string $name = null, ?bool $transform = null): array
    {
        $multipart = [];
        foreach ($filePaths as $path) {
            $multipart[] = [
                'name' => 'files',
                'contents' => fopen($path, 'r'),
                'filename' => basename($path),
            ];
        }

        if ($name) {
            $multipart[] = ['name' => 'name', 'contents' => $name];
        }
        if ($transform !== null) {
            $multipart[] = ['name' => 'transform', 'contents' => $transform ? 'true' : 'false'];
        }

        return $this->request('POST', '/api/v1/files/upload', ['multipart' => $multipart]);
    }

    public function listFiles(): array
    {
        return $this->request('GET', '/api/v1/files');
    }

    public function getFileMetadata(string $fileId): array
    {
        return $this->request('GET', "/api/v1/files/metadata/{$fileId}");
    }

    public function updateFile(string $fileId, string $filePath, ?string $name = null, ?bool $transform = null): array
    {
        $multipart = [
            [
                'name' => 'file',
                'contents' => fopen($filePath, 'r'),
                'filename' => basename($filePath),
            ],
        ];

        if ($name) {
            $multipart[] = ['name' => 'name', 'contents' => $name];
        }
        if ($transform !== null) {
            $multipart[] = ['name' => 'transform', 'contents' => $transform ? 'true' : 'false'];
        }

        return $this->request('PUT', "/api/v1/files/{$fileId}", ['multipart' => $multipart]);
    }

    public function deleteFile(string $fileId): array
    {
        return $this->request('DELETE', "/api/v1/files/{$fileId}");
    }

    public function getMetrics(): array
    {
        return $this->request('GET', '/api/v1/metrics');
    }

    public function buildFileUrl(string $fileId, array $options = []): string
    {
        if (!$options) {
            return $this->baseUrl . "/api/v1/files/{$fileId}";
        }
        $query = http_build_query($options);
        return $this->baseUrl . "/api/v1/files/{$fileId}?{$query}";
    }
}
