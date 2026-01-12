<?php

namespace App\Traits;

use App\Services\SecureFileUploadService;
use Illuminate\Http\Request;

trait SecureFileUploadTrait
{
    /**
     * Handle secure profile picture upload
     *
     * @param Request $request
     * @param string $directory
     * @param string|null $oldFileName
     * @return string|null
     * @throws \Exception
     */
    protected function handleSecureProfilePictureUpload(Request $request, string $directory = 'users', ?string $oldFileName = null): ?string
    {
        if (!$request->hasFile('picture')) {
            return null;
        }

        $fileUploadService = app(SecureFileUploadService::class);
        
        try {
            return $fileUploadService->uploadProfilePicture(
                $request->file('picture'),
                $directory,
                $oldFileName
            );
        } catch (\Exception $e) {
            throw new \Exception('File upload failed: ' . $e->getMessage());
        }
    }
}
