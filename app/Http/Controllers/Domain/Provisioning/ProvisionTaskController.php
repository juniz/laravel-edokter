<?php

namespace App\Http\Controllers\Domain\Provisioning;

use App\Http\Controllers\Controller;
use App\Models\Domain\Provisioning\ProvisionTask;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProvisionTaskController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ProvisionTask::with(['server', 'subscription.product', 'subscription.plan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $tasks = $query->latest()->paginate(15);

        return Inertia::render('admin/provision-tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only('status', 'action'),
        ]);
    }

    public function show(string $id): Response
    {
        $task = ProvisionTask::with(['server', 'subscription.product', 'subscription.plan', 'subscription.customer'])
            ->find($id);

        if (!$task) {
            abort(404);
        }

        return Inertia::render('admin/provision-tasks/Show', [
            'task' => $task,
        ]);
    }
}

