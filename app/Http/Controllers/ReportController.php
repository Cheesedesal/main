<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);
        $requests = (clone $query)->latest()->get();

        return view('reports.index', ['requests' => $requests, 'statusOptions' => DocumentRequest::STATUSES, 'summary' => ['total' => $requests->count(), 'pending' => $requests->whereIn('status', ['submitted', 'under_review'])->count(), 'ready' => $requests->where('status', 'ready')->count(), 'released' => $requests->where('status', 'released')->count()]]);
    }

    public function csv(Request $request)
    {
        $query = $this->filteredQuery($request);
        $filename = 'document-requests-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tracking code', 'Requester', 'Email', 'Document', 'Status', 'Created']);
            $query->latest()->chunk(500, function ($requests) use ($handle): void {
                foreach ($requests as $documentRequest) {
                    fputcsv($handle, [$documentRequest->tracking_code, $documentRequest->requester_name, $documentRequest->email, $documentRequest->document_type, $documentRequest->status, $documentRequest->created_at->toDateTimeString()]);
                }
            });
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function filteredQuery(Request $request)
    {
        return DocumentRequest::query()
            ->when($request->from, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($request->to, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->document_type, fn ($query, $type) => $query->where('document_type', 'like', "%{$type}%"))
            ->when($request->requester, fn ($query, $requester) => $query->where(function ($query) use ($requester) {
                $query->where('requester_name', 'like', "%{$requester}%")->orWhere('email', 'like', "%{$requester}%");
            }));
    }
}