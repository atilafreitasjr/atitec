<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'active' => true]);
    }

    public function test_admin_acessa_painel_de_usuarios_e_papeis(): void
    {
        $this->actingAs($this->admin());

        $this->get(route('admin.users.index'))->assertOk()->assertSee('Usuários');
        $this->get(route('admin.users.create'))->assertOk();
        $this->get(route('admin.roles.index'))->assertOk()->assertSee('Matriz de permissões');
    }

    public function test_gerente_e_financeiro_nao_gerenciam_usuarios(): void
    {
        foreach (['gerente', 'financeiro'] as $role) {
            $user = User::factory()->create(['role' => $role, 'active' => true]);
            $this->actingAs($user);

            $this->get(route('admin.users.index'))->assertForbidden();
            $this->get(route('admin.roles.index'))->assertForbidden();
        }
    }

    public function test_gera_usuario_com_papel_sincronizado(): void
    {
        $this->actingAs($this->admin());
        $client = Client::create(['name' => 'Cliente X', 'email' => 'x@teste.com']);

        $this->post(route('admin.users.store'), [
            'name' => 'Novo Cliente',
            'email' => 'novo@teste.com',
            'password' => 'senha-forte-123',
            'password_confirmation' => 'senha-forte-123',
            'role' => 'cliente',
            'client_id' => $client->id,
            'active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'novo@teste.com')->firstOrFail();
        $this->assertSame('cliente', $user->role);
        $this->assertTrue($user->hasRole('cliente'));
        $this->assertTrue(Hash::check('senha-forte-123', $user->password));
        $this->assertTrue($user->active);
    }

    public function test_nao_exclui_o_proprio_usuario(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->delete(route('admin.users.destroy', $admin))->assertSessionHasErrors('user');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_nao_rebaixa_ultimo_admin_ativo(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $this->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => 'financeiro',
            'active' => '1',
        ])->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_usuario_inativo_nao_faz_login(): void
    {
        $user = User::factory()->create(['role' => 'gerente', 'active' => false]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_matriz_de_permissoes_altera_o_acesso(): void
    {
        $admin = $this->admin();
        $financeiro = User::factory()->create(['role' => 'financeiro', 'active' => true]);

        $this->actingAs($financeiro);
        $this->get(route('admin.invoices.index'))->assertOk();

        // Remove invoice.view do papel financeiro pela matriz.
        $this->actingAs($admin);
        $permissions = Role::findByName('financeiro')->permissions
            ->pluck('name')->reject(fn ($p) => $p === 'invoice.view')->values()->all();

        $this->put(route('admin.roles.bulk'), ['roles' => ['financeiro' => $permissions]])
            ->assertRedirect(route('admin.roles.index'));

        $this->actingAs($financeiro->fresh());
        $this->get(route('admin.invoices.index'))->assertForbidden();
    }

    public function test_admin_sempre_mantem_todas_as_permissoes(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin);
        $this->put(route('admin.roles.bulk'), ['roles' => ['admin' => []]])
            ->assertRedirect(route('admin.roles.index'));

        $this->assertTrue(Role::findByName('admin')->permissions->count() === Permission::count());
    }

    public function test_papeis_padrao_nao_podem_ser_excluidos(): void
    {
        $this->actingAs($this->admin());

        $this->delete(route('admin.roles.destroy', Role::findByName('gerente')))
            ->assertSessionHasErrors('role');

        $this->assertNotNull(Role::where('name', 'gerente')->first());
    }

    public function test_funcionario_ve_menu_conforme_permissao(): void
    {
        $financeiro = User::factory()->create(['role' => 'financeiro', 'active' => true]);

        $this->actingAs($financeiro);
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Faturas e cobranças')
            ->assertDontSee('Papéis e permissões');
    }
}
