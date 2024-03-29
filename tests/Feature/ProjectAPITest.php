<?php

namespace Tests\Feature;

use App\Enum\UserRoleEnum;
use App\Models\Checker;
use App\Models\Client;
use App\Models\Driver;
use App\Models\GasOperator;
use App\Models\HeavyVehicle;
use App\Models\Project;
use App\Models\Station;
use App\Models\TechnicalAdmin;
use App\Models\Truck;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectAPITest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_project_api_call_index_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $projects = [];
        for ($i = 0; $i < 5; $i++) {
            $projects[] = Project::factory()->withExpectedCode()->for(Client::factory())->create();
        }

        $response = $this->json('GET', '/api/v1/projects');

        $response->assertSuccessful();

        foreach ($projects as $project) {
            $project = $project->toArray();
            $project = Arr::except($project, ['created_at', 'updated_at', 'deleted_at']);
            $this->assertDatabaseHas('projects', $project);
        }
    }

    public function test_project_api_call_create_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $project = Project::factory()
            ->for(Client::factory())
            ->make(['code' => 'AUTO'])->toArray();

        $response = $this->json('POST', '/api/v1/project', $project);

        $response->assertSuccessful();

        $project['code'] = $response['data']['code'];

        $this->assertDatabaseHas('projects', $project);
    }

    public function test_project_api_call_create_with_auto_code_with_relations_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            Driver::factory()
                ->withExpectedCode()
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            Truck::factory()
                ->withExpectedCode()
                ->for(Vendor::factory())
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            HeavyVehicle::factory()
                ->withExpectedCode()
                ->for(Vendor::factory())
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            Station::factory()
                ->withExpectedCode()
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            Checker::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            TechnicalAdmin::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            GasOperator::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
                ->create();
        }

        $project = Project::factory()
            ->for(Client::factory())
            ->make(['code' => 'AUTO'])->toArray();

        $project['drivers'] = Driver::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $project['trucks'] = Truck::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $project['heavy_vehicles'] = HeavyVehicle::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $project['stations'] = Station::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $project['checkers'] = Checker::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $project['technical_admins'] = TechnicalAdmin::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $project['gas_operators'] = GasOperator::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();

        $response = $this->json('POST', '/api/v1/project', $project);

        $response->assertSuccessful();

        $project['code'] = $response['data']['code'];
        $project = Arr::except($project, ['drivers', 'trucks', 'stations', 'heavy_vehicles', 'checkers', 'technical_admins', 'gas_operators']);

        $this->assertDatabaseHas('projects', $project);
    }

    public function test_project_api_call_show_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $project = Project::factory()->create();

        $response = $this->json('GET', '/api/v1/project/' . $project->id);

        $response->assertSuccessful();

        $project = $project->toArray();
        $project = Arr::except($project, ['created_at', 'updated_at', 'deleted_at']);
        $this->assertDatabaseHas('projects', $project);
    }

    public function test_project_api_call_read_status_expect_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $response = $this->json('GET', '/api/v1/project/read/status');

        $response->assertSuccessful();
    }

    public function test_project_api_call_update_with_auto_code_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $project = Project::factory()
            ->for(Client::factory())
            ->create();

        $updatedProject = Project::factory()
            ->for(Client::factory())
            ->make(['code' => 'AUTO'])->toArray();

        $response = $this->json('POST', '/api/v1/project/' . $project->id, $updatedProject);

        $response->assertSuccessful();

        $updatedProject['code'] = $response['data']['code'];

        $this->assertDatabaseHas('projects', $updatedProject);
    }

    public function test_project_api_call_update_with_auto_code_with_relations_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        for ($i = 0; $i < 5; $i++) {
            Driver::factory()
                ->withExpectedCode()
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            Truck::factory()
                ->withExpectedCode()
                ->for(Vendor::factory())
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            HeavyVehicle::factory()
                ->withExpectedCode()
                ->for(Vendor::factory())
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            Station::factory()
                ->withExpectedCode()
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            Checker::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::CHECKER)->first()))
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            TechnicalAdmin::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::TECHNICAL_ADMIN)->first()))
                ->create();
        }

        for ($i = 0; $i < 5; $i++) {
            GasOperator::factory()
                ->withExpectedCode()
                ->for(User::factory()->hasAttached(Role::where('name', '=', UserRoleEnum::GAS_OPERATOR)->first()))
                ->create();
        }

        $project = Project::factory()
            ->for(Client::factory())
            ->create();

        $updatedProject = Project::factory()
            ->for(Client::factory())
            ->make(['code' => 'AUTO'])->toArray();

        $updatedProject['drivers'] = Driver::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $updatedProject['trucks'] = Truck::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $updatedProject['heavy_vehicles'] = HeavyVehicle::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $updatedProject['stations'] = Station::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $updatedProject['checkers'] = Checker::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $updatedProject['technical_admins'] = TechnicalAdmin::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();
        $updatedProject['gas_operators'] = GasOperator::inRandomOrder()->limit(mt_rand(1, 5))->get()->pluck('id')->toArray();

        $response = $this->json('POST', '/api/v1/project/' . $project->id, $updatedProject);

        $response->assertSuccessful();

        $updatedProject['code'] = $response['data']['code'];
        $updatedProject = Arr::except($updatedProject, ['drivers', 'trucks', 'heavy_vehicles', 'stations', 'checkers', 'technical_admins', 'gas_operators']);

        $this->assertDatabaseHas('projects', $updatedProject);
    }

    public function test_project_api_call_update_with_existing_code_in_same_project_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $project = Project::factory()
            ->for(Client::factory())
            ->create();

        $updatedProject = Project::factory()
            ->for(Client::factory())
            ->make(['code' => $project->code])->toArray();

        $response = $this->json('POST', '/api/v1/project/' . $project->id, $updatedProject);

        $response->assertSuccessful();

        $this->assertDatabaseHas('projects', $updatedProject);
    }

    public function test_project_api_call_update_with_existing_code_in_different_project_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $existingProject = Project::factory()
            ->for(Client::factory())
            ->create();

        $newProject = Project::factory()
            ->for(Client::factory())
            ->create();

        $updatedProject = Project::factory()
            ->for(Client::factory())
            ->make(['code' => $existingProject->code])->toArray();

        $response = $this->json('POST', '/api/v1/project/' . $newProject->id, $updatedProject);

        $response->assertStatus(422);
    }

    public function test_project_api_call_delete_expect_success()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoleEnum::ADMIN)->first())
            ->create();

        $this->actingAs($user);

        $project = Project::factory()
            ->for(Client::factory())
            ->create();

        $response = $this->json('DELETE', '/api/v1/project/' . $project->id);

        $response->assertSuccessful();

        $project = $project->toArray();
        $project = Arr::except($project, ['created_at', 'updated_at', 'deleted_at']);
        $this->assertSoftDeleted('projects', $project);
    }
}
