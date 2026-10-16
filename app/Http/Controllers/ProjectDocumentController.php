<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'in:contrato,escopo,entrega,outro'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg', 'max:20480'],
            'visible_to_client' => ['nullable', 'boolean'],
        ]);

        $file = $data['file'];
        $path = $file->store('project-documents/'.$project->id, 'local');

        $project->documents()->create([
            'uploaded_by' => $request->user()->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'visible_to_client' => $request->boolean('visible_to_client'),
        ]);

        return back()->with('success', 'Documento salvo no espaço do projeto.');
    }

    public function destroy(Project $project, ProjectDocument $document): RedirectResponse
    {
        abort_unless($document->project_id === $project->id, 404);

        Storage::disk('local')->delete($document->path);
        $document->delete();

        return back()->with('success', 'Documento removido.');
    }

    public function download(Request $request, Project $project, ProjectDocument $document): StreamedResponse
    {
        abort_unless($document->project_id === $project->id, 404);

        $user = $request->user();
        $isStaff = in_array($user->role, ['admin', 'gerente', 'financeiro'], true) && $user->can('project.view');
        $ownsProject = $user->client_id !== null && $user->client_id === $project->client_id;

        abort_unless($isStaff || ($ownsProject && $document->visible_to_client), 404);
        abort_unless(Storage::disk('local')->exists($document->path), 404);

        $extension = pathinfo($document->path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download(
            $document->path,
            $document->name.($extension ? '.'.$extension : ''),
        );
    }
}
