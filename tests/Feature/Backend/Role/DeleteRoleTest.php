<?php

namespace Tests\Feature\Backend\Role;

use App\Events\Backend\Auth\Role\RoleDeleted;
use App\Models\Auth\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DeleteRoleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_role_can_be_deleted()
    {
        $role = factory(Role::class)->create();
        $this->loginAsAdmin();

        $this->assertDatabaseHas(config('permission.table_names.roles'), ['id' => $role->id]);

        $this->delete("https://admin.belsaa.com/auth/role/{$role->id}");

        $this->assertDatabaseMissing(config('permission.table_names.roles'), ['id' => $role->id]);
    }

    /** @test */
    public function a_role_with_assigned_users_cant_be_deleted()
    {
        $this->loginAsAdmin();

        $role = Role::whereName(config('access.users.admin_role'))->first();
        $response = $this->delete('https://admin.belsaa.com/auth/role/'.$role->id);

        $response->assertSessionHas(['flash_danger' => __('exceptions.backend.access.roles.cant_delete_admin')]);
    }

    /** @test */
    public function an_event_gets_dispatched()
    {
        $role = factory(Role::class)->create();
        Event::fake();
        $this->loginAsAdmin();

        $this->delete("https://admin.belsaa.com/auth/role/{$role->id}");

        Event::assertDispatched(RoleDeleted::class);
    }
}
