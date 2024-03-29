<?php

namespace App\Repositories;

use App\Enum\ProjectStatusEnum;
use App\Interfaces\ProjectRepositoryInterface;
use App\Models\Project;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function getAllProjects()
    {
        $projects = Project::orderBy('name', 'asc')->get();

        return $projects;
    }

    public function create(array $data)
    {
        $project = new Project();
        $project->code = $data['code'];
        $project->name = $data['name'];
        $project->description = $data['description'];
        $project->start_date = $data['start_date'];
        $project->end_date = $data['end_date'];
        $project->person_in_charge = $data['person_in_charge'];
        $project->amount = $data['amount'];
        $project->client_id = $data['client_id'];
        $project->province = $data['province'];
        $project->regency = $data['regency'];
        $project->district = $data['district'];
        $project->subdistrict = $data['subdistrict'];
        $project->status = $data['status'];
        $project->save();

        if (isset($data['drivers'])) {
            $project->drivers()->attach($data['drivers']);
        }
        if (isset($data['trucks'])) {
            $project->trucks()->attach($data['trucks']);
        }
        if (isset($data['heavy_vehicles'])) {
            $project->heavyVehicles()->attach($data['heavy_vehicles']);
        }
        if (isset($data['stations'])) {
            $project->stations()->attach($data['stations']);
        }
        if (isset($data['checkers'])) {
            $project->checkers()->attach($data['checkers']);
        }
        if (isset($data['technical_admins'])) {
            $project->technicalAdmins()->attach($data['technical_admins']);
        }
        if (isset($data['gas_operators'])) {
            $project->gasOperators()->attach($data['gas_operators']);
        }

        return $project;
    }

    public function getProjectById(string $id)
    {
        $project = Project::find($id);

        return $project;
    }

    public function getProjectStatus()
    {
        $statuses = [];
        foreach (ProjectStatusEnum::toArray() as $status) {
            $statuses[] = [
                'name' => $status,
            ];
        }

        return $statuses;
    }

    public function update(array $data, string $id)
    {
        $project = Project::find($id);
        $project->code = $data['code'];
        $project->name = $data['name'];
        $project->description = $data['description'];
        $project->start_date = $data['start_date'];
        $project->end_date = $data['end_date'];
        $project->person_in_charge = $data['person_in_charge'];
        $project->amount = $data['amount'];
        $project->client_id = $data['client_id'];
        $project->province = $data['province'];
        $project->regency = $data['regency'];
        $project->district = $data['district'];
        $project->subdistrict = $data['subdistrict'];
        $project->status = $data['status'];
        $project->save();

        if (isset($data['drivers'])) {
            $project->drivers()->sync($data['drivers']);
        }
        if (isset($data['trucks'])) {
            $project->trucks()->sync($data['trucks']);
        }
        if (isset($data['heavy_vehicles'])) {
            $project->heavyVehicles()->sync($data['heavy_vehicles']);
        }
        if (isset($data['stations'])) {
            $project->stations()->sync($data['stations']);
        }
        if (isset($data['checkers'])) {
            $project->checkers()->sync($data['checkers']);
        }
        if (isset($data['technical_admins'])) {
            $project->technicalAdmins()->sync($data['technical_admins']);
        }
        if (isset($data['gas_operators'])) {
            $project->gasOperators()->sync($data['gas_operators']);
        }

        return $project;
    }

    public function delete(string $id)
    {
        $project = Project::find($id);
        $project->delete();

        return $project;
    }

    public function generateCode(int $tryCount): string
    {
        $count = Project::withTrashed()->count() + 1 + $tryCount;
        $code = 'PRJ'.str_pad($count, 4, '0', STR_PAD_LEFT);

        return $code;
    }

    public function isUniqueCode(string $code, $exceptId = null): bool
    {
        $query = Project::where('code', $code);
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->doesntExist();
    }
}
