<?php

namespace Tests\Feature\Auth;

use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'phone' => '(41) 99999-0000',
            'cpf' => '529.982.247-25',
            'zip_code' => '80000-000',
            'address' => 'Rua XV de Novembro',
            'address_number' => '100',
            'address_complement' => 'Sala 2',
            'neighborhood' => 'Centro',
            'city' => 'Curitiba',
            'state' => 'PR',
        ], $overrides);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Criar conta de cliente')
            ->assertSee('Pelo menos 8 caracteres')
            ->assertSee('Mostrar senha');
    }

    public function test_weak_password_is_rejected(): void
    {
        $response = $this->post('/register', $this->payload([
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]));

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_new_users_can_register_as_client_with_full_data(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $response = $this->post('/register', $this->payload());

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('cliente', $user->role);
        $this->assertTrue($user->hasRole('cliente'));

        $client = Client::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('52998224725', $client->cpf);
        $this->assertSame('529.982.247-25', $client->formatted_cpf);
        $this->assertSame('Curitiba', $client->city);
        $this->assertSame('80000000', $client->zip_code);
        $this->assertNull($client->cnpj);
        $this->assertSame($user->id, $client->user_id);
        $this->assertSame($client->id, $user->client_id);

        // Após o registro cai direto no portal do cliente.
        $this->get('/dashboard')->assertRedirect(route('cliente.dashboard', absolute: false));
    }

    public function test_company_data_is_saved_when_user_has_company(): void
    {
        $response = $this->post('/register', $this->payload([
            'has_company' => '1',
            'company' => 'ACME Ltda',
            'cnpj' => '11.222.333/0001-81',
            'company_same_address' => '1',
        ]));

        $response->assertRedirect(route('dashboard', absolute: false));

        $client = Client::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('ACME Ltda', $client->company);
        $this->assertSame('11222333000181', $client->cnpj);
        $this->assertSame('11.222.333/0001-81', $client->formatted_cnpj);
        $this->assertTrue($client->company_same_address);
        $this->assertNull($client->company_address);
    }

    public function test_company_can_have_a_different_address(): void
    {
        $response = $this->post('/register', $this->payload([
            'has_company' => '1',
            'company' => 'ACME Ltda',
            'cnpj' => '11.222.333/0001-81',
            'company_zip_code' => '01.300-100',
            'company_address' => 'Av. Paulista',
            'company_address_number' => '1000',
            'company_address_complement' => 'Conj. 101',
            'company_neighborhood' => 'Bela Vista',
            'company_city' => 'São Paulo',
            'company_state' => 'SP',
        ]));

        $response->assertRedirect(route('dashboard', absolute: false));

        $client = Client::where('email', 'test@example.com')->firstOrFail();
        $this->assertFalse($client->company_same_address);
        $this->assertSame('01300100', $client->company_zip_code);
        $this->assertSame('Av. Paulista', $client->company_address);
        $this->assertSame('São Paulo', $client->company_city);
        $this->assertSame('SP', $client->company_state);
        $this->assertStringContainsString('Av. Paulista, 1000', (string) $client->company_full_address);
    }

    public function test_company_data_is_required_when_user_has_company(): void
    {
        $response = $this->post('/register', $this->payload(['has_company' => '1']));

        $response->assertSessionHasErrors([
            'company', 'cnpj', 'company_zip_code', 'company_address',
            'company_address_number', 'company_neighborhood', 'company_city', 'company_state',
        ]);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_company_same_address_skips_company_address_validation(): void
    {
        $response = $this->post('/register', $this->payload([
            'has_company' => '1',
            'company' => 'ACME Ltda',
            'cnpj' => '11.222.333/0001-81',
            'company_same_address' => '1',
        ]));

        $response->assertSessionDoesntHaveErrors(['company_zip_code', 'company_address', 'company_state']);
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_invalid_cpf_is_rejected(): void
    {
        $response = $this->post('/register', $this->payload(['cpf' => '111.111.111-11']));

        $response->assertSessionHasErrors('cpf');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_de_cliente_pre_cadastrado_recebe_aviso_e_nao_cria_usuario(): void
    {
        $client = Client::create(['name' => 'Cliente Pre', 'email' => 'pre@teste.com']);

        $response = $this->post('/register', $this->payload(['email' => 'pre@teste.com']));

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertNull($client->fresh()->user_id);
    }

    public function test_duplicate_cpf_is_rejected(): void
    {
        Client::create(['name' => 'Anterior', 'email' => 'anterior@teste.com', 'cpf' => '52998224725']);

        $response = $this->post('/register', $this->payload());

        $response->assertSessionHasErrors('cpf');
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_required_address_fields_are_validated(): void
    {
        $response = $this->post('/register', $this->payload([
            'zip_code' => '',
            'address' => '',
            'city' => '',
            'state' => 'XX',
        ]));

        $response->assertSessionHasErrors(['zip_code', 'address', 'city', 'state']);
        $this->assertGuest();
    }
}
