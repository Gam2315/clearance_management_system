<?php

namespace App\Http\Controllers;

use App\Services\SecureFileUploadService;
use Illuminate\Http\Request;

class SecureFileController extends Controller
{
    private SecureFileUploadService $fileService;

    public function __construct(SecureFileUploadService $fileService)
    {
        $this->fileService = $fileService;
        $this->middleware('auth'); // Require authentication to access files
    }

    /**
     * Serve secure files
     */
    public function serve(Request $request, string $directory, string $filename)
    {
        // Additional authorization checks can be added here
        // For example, users should only access their own profile pictures
        
        return $this->fileService->serveFile($directory, $filename);
    }
}
