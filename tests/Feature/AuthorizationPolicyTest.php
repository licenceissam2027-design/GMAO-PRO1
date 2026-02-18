<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_cannot_access_team_management_page(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get(route('team.index'));

        $response->assertForbidden();
    }

    public function test_technician_cannot_create_project(): void
    {
        $technician = User::factory()->create(['role' => 'technician']);

        $response = $this->actingAs($technician)->post(route('projects.store'), [
            'name' => 'Unauthorized Project',
            'code' => 'UNAUTH-001',
            'priority' => 'high',
            'status' => 'planned',
        ]);

        $response->assertForbidden();
    }

    public function test_employee_cannot_access_experts_page(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $response = $this->actingAs($employee)->get(route('assets.experts'));

        $response->assertForbidden();
    }
}

