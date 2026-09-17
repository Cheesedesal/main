<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\RequestStatusHistory;
use App\Notifications\RequestStatusUpdated;

class DocumentRequestController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $query = DocumentRequest::query();

        if ($user->isAdmin() && request()->boolean('archived')) {
            $query->onlyTrashed();
        }

        if ($user->role === 'user') {
            $query->where('created_by', $user->id);
        }

        $query->when(request('search'), function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('tracking_code', 'like', "%{$search}%")
                    ->orWhere('requester_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('document_type', 'like', "%{$search}%");
            });
        })->when(request('status'), fn ($query, $status) => $query->where('status', $status));
        $query->when(request('document_type'), fn ($query, $type) => $query->where('document_type', $type))
            ->when(request('date_from'), fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when(request('date_to'), fn ($query, $date) => $query->whereDate('created_at', '<=', $date));

        $sort = in_array(request('sort'), ['created_at', 'requester_name', 'status'], true) ? request('sort') : 'created_at';
        $direction = request('direction') === 'asc' ? 'asc' : 'desc';

        return view('requests.index', [
            'requests' => $query->orderBy($sort, $direction)->paginate(10)->withQueryString(),
            'statusOptions' => DocumentRequest::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('requests.create');
    }

    public function show(DocumentRequest $documentRequest): View
    {
        abort_unless(request()->user()->isStaff() || $documentRequest->created_by === request()->user()->id, 403);

        return view('requests.show', ['documentRequest' => $documentRequest->load(['statusHistory.changer', 'files'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'requester_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:40'],
            'document_type' => ['required', 'string', 'max:100'],
            'purpose' => ['required', 'string', 'max:255'],
            'copies' => ['required', 'integer', 'min:1', 'max:10'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);

        $documentRequest = DocumentRequest::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'tracking_code' => 'DOC-'.str()->upper(str()->random(8)),
        ]);

        ActivityLogger::record('created_request', "{$request->user()->name} created {$documentRequest->tracking_code}.", $documentRequest, $request);
        RequestStatusHistory::create(['document_request_id' => $documentRequest->id, 'changed_by' => $request->user()->id, 'status' => 'submitted', 'note' => 'Request submitted.']);
        $this->storeFiles($request, $documentRequest);
        $request->user()->notify(new RequestStatusUpdated($documentRequest, "Your request {$documentRequest->tracking_code} was submitted."));

        return to_route('requests.index')->with('success', "Request {$documentRequest->tracking_code} submitted successfully.");
    }

    public function updateStatus(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', array_keys(DocumentRequest::STATUSES))],
            'staff_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($request->user()->isStaff(), 403);
        $documentRequest->update([
            ...$validated,
            'processed_at' => in_array($validated['status'], ['ready', 'released', 'declined'], true) ? now() : null,
        ]);

        ActivityLogger::record('updated_request', "{$request->user()->name} updated {$documentRequest->tracking_code} to {$validated['status']}.", $documentRequest, $request);
        RequestStatusHistory::create(['document_request_id' => $documentRequest->id, 'changed_by' => $request->user()->id, 'status' => $validated['status'], 'note' => $validated['staff_notes'] ?? null]);
        if ($documentRequest->creator) {
            $documentRequest->creator->notify(new RequestStatusUpdated($documentRequest, "Your request {$documentRequest->tracking_code} is now {$validated['status']} ."));
        }

        return to_route('requests.index')->with('success', "{$documentRequest->tracking_code} updated.");
    }

    public function edit(DocumentRequest $documentRequest): View
    {
        abort_unless(request()->user()->isStaff() || $documentRequest->created_by === request()->user()->id, 403);

        return view('requests.edit', compact('documentRequest'));
    }

    public function update(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        abort_unless($request->user()->isStaff(), 403);
        $validated = $request->validate([
            'requester_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'student_id' => ['nullable', 'string', 'max:40'],
            'document_type' => ['required', 'string', 'max:100'],
            'purpose' => ['required', 'string', 'max:255'],
            'copies' => ['required', 'integer', 'min:1', 'max:10'],
            'status' => ['required', 'in:'.implode(',', array_keys(DocumentRequest::STATUSES))],
            'staff_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $documentRequest->update($validated + ['processed_at' => in_array($validated['status'], ['ready', 'released', 'declined'], true) ? now() : null]);
        ActivityLogger::record('updated_request', "{$request->user()->name} edited {$documentRequest->tracking_code}.", $documentRequest, $request);
        RequestStatusHistory::create(['document_request_id' => $documentRequest->id, 'changed_by' => $request->user()->id, 'status' => $validated['status'], 'note' => $validated['staff_notes'] ?? null]);
        if ($documentRequest->creator) {
            $documentRequest->creator->notify(new RequestStatusUpdated($documentRequest, "Your request {$documentRequest->tracking_code} is now {$validated['status']} ."));
        }

        return to_route('requests.index')->with('success', 'Request updated.');
    }

    public function destroy(Request $request, DocumentRequest $documentRequest): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $code = $documentRequest->tracking_code;
        ActivityLogger::record('deleted_request', "{$request->user()->name} deleted {$code}.", $documentRequest, $request);
        $documentRequest->delete();

        return to_route('requests.index')->with('success', 'Request deleted.');
    }

    public function restore(Request $request, int $documentRequest): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $archivedRequest = DocumentRequest::onlyTrashed()->findOrFail($documentRequest);
        $archivedRequest->restore();
        ActivityLogger::record('restored_request', "{$request->user()->name} restored {$archivedRequest->tracking_code}.", $archivedRequest, $request);

        return to_route('requests.index')->with('success', 'Request restored.');
    }

    private function storeFiles(Request $request, DocumentRequest $documentRequest): void
    {
        foreach ($request->file('files', []) as $file) {
            $path = $file->store('document-requests/'.$documentRequest->id, 'local');
            $documentRequest->files()->create(['uploaded_by' => $request->user()->id, 'disk' => 'local', 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'size' => $file->getSize()]);
            ActivityLogger::record('uploaded_file', "{$request->user()->name} uploaded a file for {$documentRequest->tracking_code}.", $documentRequest, $request);
        }
    }
}