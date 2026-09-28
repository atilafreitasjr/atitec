<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deliverable;
use App\Models\DeliverableTask;
use App\Models\Project;
use Illuminate\View\View;

class ProjectKanbanController extends Controller
{
    public function kanban(Project $project): View
    {
        return view('admin.projects.kanban', compact('project'));
    }

    public function deliverablesIndex(Project $project): View
    {
        return view('admin.projects.deliverables-index', compact('project'));
    }

    public function deliverableCreate(Project $project): View
    {
        return view('admin.projects.deliverable-form', compact('project') + ['deliverableId' => null]);
    }

    public function deliverableShow(Project $project, Deliverable $deliverable): View
    {
        abort_unless($deliverable->project_id === $project->id, 404);

        return view('admin.projects.deliverable-show', compact('project', 'deliverable'));
    }

    public function deliverableEdit(Project $project, Deliverable $deliverable): View
    {
        abort_unless($deliverable->project_id === $project->id, 404);

        return view('admin.projects.deliverable-form', compact('project') + ['deliverableId' => $deliverable->id]);
    }

    public function tasksIndex(Project $project): View
    {
        return view('admin.projects.tasks-index', compact('project'));
    }

    public function taskCreate(Project $project): View
    {
        return view('admin.projects.task-form', compact('project') + ['taskId' => null]);
    }

    public function taskEdit(Project $project, DeliverableTask $task): View
    {
        abort_unless($task->project_id === $project->id, 404);

        return view('admin.projects.task-form', compact('project') + ['taskId' => $task->id]);
    }
}
