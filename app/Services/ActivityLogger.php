<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(Project $project, Model $subject, string $event, string $description, array $changes = []): void
    {
        ActivityLog::create([
            'organization_id' => $project->organization_id,
            'project_id' => $project->id,
            'subject_type' => class_basename($subject),
            'subject_id' => $subject->getKey(),
            'causer_id' => auth()->id(),
            'event' => $event,
            'description' => $description,
            'changes' => $changes ?: null,
        ]);
    }
}
