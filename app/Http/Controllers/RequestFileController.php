<?php

namespace App\Http\Controllers;

use App\Models\RequestFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequestFileController extends Controller
{
    public function download(Request $request, RequestFile $requestFile)
    {
        $documentRequest = $requestFile->request;
        abort_unless($request->user()->isStaff() || $documentRequest->created_by === $request->user()->id, 403);
        abort_unless(Storage::disk($requestFile->disk)->exists($requestFile->path), 404);

        return Storage::disk($requestFile->disk)->download($requestFile->path, $requestFile->original_name);
    }
}