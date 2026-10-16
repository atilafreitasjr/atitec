<?php

namespace Tests\Feature\Auth;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function fakeGoogleUser(array $attributes = []): void
    {
        $user = (new SocialiteUser)->map(array_merge([
            'id' => 'google-123',
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'avatar' => 'https://example.com/avatar.png',
        ], $attributes));

        Socialite::fake('google', $user);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function clientPayload(array $overrides = []): array
    {
        return array_merge([
            'phone' => '(41) 98888-0000',
            'cpf' => '111.444.777-35',
            'zip_code' => '80000-000',
            'address' => 'Rua das Flores',
            'address_number' => '10',
            'neighborhood' => 'Centro',
            'city' => 'Curitiba',
            'state' => 'PR',
        ], $overrides);
    }

    public function test_login_page_shows_google_button_only_when_configured(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get('/login')->assertOk()->assertDontSee('Entrar com Google', false);

        config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);

        $this->get('/login')->assertOk()->assertSee('Entrar com Google', false);

        // O login por e-mail/senha continua disponível em qualquer cenário.
        $this->get('/login')->assertOk()->assertSee('Esqueceu a senha?', false);
    }

    public function test_login_page_shows_google_button_in_local_even_without_keys(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);
        $this->app['env'] = 'local';

        $this->get('/login')->assertOk()->assertSee('Entrar com Google', false);

        // Sem as chaves, clicar volta ao login com aviso amigável (sem erro).
        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_redirect_goes_to_google_when_configured(): void
    {
        config(['services.google.client_id' => 'id', 'services.google.client_secret' => 'secret']);
        Socialite::fake('google');

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://socialite.fake/google/authorize');
    }

    public function test_redirect_back_to_login_when_not_configured(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
    }

    public function test_new_google_user_is_created_as_client_and_sent_to_complete_registration(): void
    {
        $this->fakeGoogleUser();

        $this->get(route('auth.google.callback'))->assertRedirect(route('cadastro.completar'));

        $this->assertAuthenticated();
        $this->get(route('cadastro.completar'))->assertOk()->assertSee('Complete seu cadastro');

        $user = User::where('email', 'maria@example.com')->firstOrFail();
        $this->assertSame('cliente', $user->role);
        $this->assertSame('google-123', $user->google_id);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_existing_user_by_email_gets_google_linked(): void
    {
        $client = Client::create(['name' => 'Maria', 'email' => 'maria@example.com']);
        $user = User::factory()->create([
            'email' => 'maria@example.com',
            'role' => 'cliente',
            'client_id' => $client->id,
        ]);

        $this->fakeGoogleUser(['id' => 'google-999']);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertSame('google-999', $user->fresh()->google_id);
    }

    public function test_google_login_links_client_pre_registered_by_email(): void
    {
        $client = Client::create(['name' => 'Maria', 'email' => 'maria@example.com']);
        $user = User::factory()->create(['email' => 'maria@example.com', 'role' => 'cliente']);

        $this->fakeGoogleUser();

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('dashboard', absolute: false));

        $user->refresh();
        $this->assertSame($client->id, $user->client_id);
        $this->assertSame($user->id, $client->fresh()->user_id);
    }

    public function test_google_user_can_define_password_and_login_without_google(): void
    {
        $this->fakeGoogleUser();
        $this->get(route('auth.google.callback'));

        $user = User::where('email', 'maria@example.com')->firstOrFail();
        $this->assertNull($user->password);
        $this->assertAuthenticatedAs($user);

        // O perfil orienta que a conta entra pelo Google e que dá para definir uma senha.
        $this->get('/profile')->assertOk()->assertSee('Você entra com o Google');

        $this->from('/profile')->put('/password', [
            'password' => 'senha-sem-google-123',
            'password_confirmation' => 'senha-sem-google-123',
        ])->assertSessionHasNoErrors()->assertRedirect('/profile');

        $this->assertTrue(Hash::check('senha-sem-google-123', $user->fresh()->password));

        $this->post('/logout');

        // Agora entra digitando e-mail e senha, sem depender do Google.
        $this->post('/login', [
            'email' => 'maria@example.com',
            'password' => 'senha-sem-google-123',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_inactive_google_user_is_rejected(): void
    {
        User::factory()->create(['email' => 'maria@example.com', 'active' => false]);

        $this->fakeGoogleUser();

        $this->get(route('auth.google.callback'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_google_user_completes_registration(): void
    {
        $this->fakeGoogleUser();
        $this->get(route('auth.google.callback'));

        $response = $this->post(route('cadastro.completar.store'), $this->clientPayload([
            'has_company' => '1',
            'company' => 'Silva ME',
            'cnpj' => '04.252.011/0001-10',
            'company_same_address' => '1',
        ]));

        $response->assertRedirect(route('cliente.dashboard'));

        $user = User::where('email', 'maria@example.com')->firstOrFail();
        $client = Client::where('email', 'maria@example.com')->firstOrFail();

        $this->assertSame($client->id, $user->client_id);
        $this->assertSame($user->id, $client->user_id);
        $this->assertSame('Silva ME', $client->company);
        $this->assertSame('04252011000110', $client->cnpj);

        // Dashboard agora leva ao portal.
        $this->get('/dashboard')->assertRedirect(route('cliente.dashboard', absolute: false));
    }

    public function test_complete_registration_redirects_when_already_linked(): void
    {
        $client = Client::create(['name' => 'Maria', 'email' => 'maria@example.com']);
        $user = User::factory()->create(['email' => 'maria@example.com', 'client_id' => $client->id]);

        $this->actingAs($user)
            ->get(route('cadastro.completar'))
            ->assertRedirect(route('dashboard', absolute: false));
    }
}
