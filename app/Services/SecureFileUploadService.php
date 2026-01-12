<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class SecureFileUploadService
{
    /**
     * Allowed file types for profile pictures
     */
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'image/gif'
    ];

    /**
     * Allowed file extensions for profile pictures
     */
    private const ALLOWED_IMAGE_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif'
    ];

    /**
     * Maximum file size in bytes (5MB)
     */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Upload and validate a profile picture
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $oldFileName
     * @return string
     * @throws Exception
     */
    public function uploadProfilePicture(UploadedFile $file, string $directory = 'profile-pictures', ?string $oldFileName = null): string
    {
        // Validate file
        $this->validateImageFile($file);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);

        // Create directory if it doesn't exist
        $fullPath = storage_path('app/private/' . $directory);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        // Move file to secure location (outside public directory)
        $file->storeAs('private/' . $directory, $filename);

        // Delete old file if exists
        if ($oldFileName) {
            $this->deleteFile($directory . '/' . $oldFileName);
        }

        // File uploaded successfully

        return $filename;
    }

    /**
     * Validate image file
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    private function validateImageFile(UploadedFile $file): void
    {
        // Check if file was uploaded successfully
        if (!$file->isValid()) {
            throw new Exception('File upload failed.');
        }

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            throw new Exception('File size exceeds maximum allowed size of 5MB.');
        }

        // Check MIME type
        if (!in_array($file->getMimeType(), self::ALLOWED_IMAGE_TYPES)) {
            throw new Exception('Invalid file type. Only JPEG, PNG, and GIF files are allowed.');
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
            throw new Exception('Invalid file extension.');
        }

        // Additional security: Check if file is actually an image
        $imageInfo = getimagesize($file->getPathname());
        if ($imageInfo === false) {
            throw new Exception('File is not a valid image.');
        }

        // Check for embedded PHP code (basic check)
        $fileContent = file_get_contents($file->getPathname());
        if (strpos($fileContent, '<?php') !== false || strpos($fileContent, '<?=') !== false) {
            throw new Exception('File contains potentially malicious content.');
        }
    }

    /**
     * Generate secure filename
     *
     * @param UploadedFile $file
     * @return string
     */
    private function generateSecureFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return time() . '_' . Str::random(32) . '.' . $extension;
    }

    /**
     * Delete a file
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteFile(string $filePath): bool
    {
        $fullPath = 'private/' . $filePath;
        
        if (Storage::exists($fullPath)) {
            return Storage::delete($fullPath);
        }
        
        return true;
    }

    /**
     * Get file URL for serving
     *
     * @param string $filename
     * @param string $directory
     * @return string
     */
    public function getFileUrl(string $filename, string $directory = 'profile-pictures'): string
    {
        return route('secure-file', ['directory' => $directory, 'filename' => $filename]);
    }

    /**
     * Serve file securely (to be used in a controller)
     *
     * @param string $directory
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|null
     */
    public function serveFile(string $directory, string $filename)
    {
        $filePath = storage_path('app/private/' . $directory . '/' . $filename);
        
        // Security check: ensure file is within allowed directory
        $realPath = realpath($filePath);
        $allowedPath = realpath(storage_path('app/private/' . $directory));
        
        if (!$realPath || !$allowedPath || strpos($realPath, $allowedPath) !== 0) {
            abort(404);
        }
        
        if (!file_exists($filePath)) {
            abort(404);
        }
        
        return response()->file($filePath);
    }
}
