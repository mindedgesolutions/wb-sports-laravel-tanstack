<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    public function download(Request $request)
    {
        $filePath = str_replace('/storage', '', $request->filePath);

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found.');
        }

        $filename = $request->fileName . '.' . pathinfo($filePath, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($filePath, $filename);
    }
}
