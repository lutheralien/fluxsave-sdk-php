# fluxsave/sdk

PHP SDK for FluxSave. API key + secret authentication with file upload and management helpers.

## Install

```bash
composer require fluxsave/sdk
```

## Usage

```php
use Fluxsave\FluxsaveClient;

$client = new FluxsaveClient(
    'https://fluxsaveapi.lutheralien.com',
    'fs_xxx',
    'sk_xxx'
);

$response = $client->uploadFile('./photo.png', 'marketing-hero', true);
print_r($response);
```

## API

- `uploadFile($path, $name = null, $transform = null)`
- `uploadFiles(array $paths, $name = null, $transform = null)`
- `listFiles()`
- `getFileMetadata($fileId)`
- `updateFile($fileId, $path, $name = null, $transform = null)`
- `deleteFile($fileId)`
- `getMetrics()`
- `buildFileUrl($fileId, array $options = [])`

## Docs

https://fluxsave-sdk-docs.vercel.app/ (SDK reference + examples)
