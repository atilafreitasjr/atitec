<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('admin.projects.index', [
            'projects' => Project::with('client')->orderBy('sort_order')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.projects.form', [
            'project' => new Project,
            'clients' => Client::orderBy('name')->get(),
            'statuses' => Project::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);

        Project::create($data);

        return redirect()->route('admin.projects.index')->with('success', 'Projeto criado.');
    }

    public function show(Project $project): View
    {
        return view('admin.projects.show', [
            'project' => $project->load(['client', 'tasks', 'invoices']),
        ]);
    }

    public function edit(Project $project): View
    {
        return view('admin.projects.form', [
            'project' => $project,
            'clients' => Client::orderBy('name')->get(),
            'statuses' => Project::STATUSES,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $this->validated($request, $project->id);
        $data['slug'] = Str::slug($data['slug'] ?: $data['title']);

        $project->update($data);

        return redirect()->route('admin.projects.index')->with('success', 'Projeto atualizado.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Projeto excluído.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'client_id' => ['nullable', 'exists:clients,id'],
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:projects,slug,'.$ignoreId],
            'description' => ['required', 'string'],
            'problem' => ['nullable', 'string'],
            'solution' => ['nullable', 'string'],
            'results' => ['nullable', 'string'],
            'url' => ['nullable', 'url', 'max:255'],
            'segment' => ['nullable', 'string', 'max:120'],
            'technologies' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:'.implode(',', array_keys(Project::STATUSES))],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'string', 'max:255'],
            'featured' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);
    }
}
