<?php

namespace App\Services;

use Supabase\Storage\StorageClient;

class SupabaseService
{
    protected $storageClient;

    public function __construct(StorageClient $storageClient)
    {
        $this->storageClient = $storageClient;
    }

    public function uploadFile($path, $fileContent)
    {
        // Asumiendo que el método `upload` de `StorageClient` necesita tres argumentos
        $options = []; // Puedes ajustar esto según las necesidades
        $this->storageClient->upload($path, $fileContent, $options);
    }
}

