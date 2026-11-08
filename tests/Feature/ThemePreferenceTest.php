<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Theme;
use Database\Seeders\ChannelSeeder;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemePreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_save_light_theme_and_it_is_applied_to_admin_layout(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'theme' => 'dark',
        ]);

        $this->actingAs($user)
            ->post(route('theme.update'), ['theme' => 'light'])
            ->assertRedirect();

        $this->assertSame('light', $user->fresh()->theme);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('at-theme-light', false)
            ->assertSee('data-theme-switch', false);
    }

    public function test_invalid_theme_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post(route('theme.update'), ['theme' => 'solarized'])
            ->assertSessionHasErrors('theme');

        $this->assertSame('dark', $user->fresh()->theme);
    }

    public function test_home_defaults_to_dark_theme(): void
    {
        $this->seed(ProjectSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-theme="light"', false)
            ->assertDontSee('theme-light.css', false);
    }

    public function test_visitor_can_select_light_theme_for_home(): void
    {
        $this->seed(ProjectSeeder::class);

        $this->from('/')
            ->post(route('theme.update'), ['theme' => 'light'])
            ->assertRedirect('/');

        $this->assertSame('light', session(Theme::COOKIE));

        $this->get('/')
            ->assertOk()
            ->assertSee('data-theme="light"', false)
            ->assertSee('atitec/assets/css/theme-light.css', false)
            ->assertSee('data-site-theme-switch', false);
    }

    public function test_logged_user_saved_light_theme_applies_to_home(): void
    {
        $this->seed(ProjectSeeder::class);

        $user = User::factory()->create([
            'role' => 'cliente',
            'theme' => 'light',
        ]);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('data-theme="light"', false)
            ->assertSee('atitec/assets/css/theme-light.css', false);
    }

    public function test_guest_light_choice_is_kept_after_login(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'active' => true,
            'theme' => 'dark',
        ]);

        $this->withSession([Theme::COOKIE => 'light'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect();

        $this->assertSame('light', $user->fresh()->theme);
    }

    public function test_light_theme_applies_to_internal_public_pages_and_cartao(): void
    {
        $this->seed(ChannelSeeder::class);
        $this->seed(ProjectSeeder::class);

        $pages = [
            '/sobre',
            '/contato',
            '/portfolio',
            '/servicos/desenvolvimento-sob-medida',
            '/orcamento',
        ];

        foreach ($pages as $url) {
            $this->withSession([Theme::COOKIE => 'light'])
                ->get($url)
                ->assertOk()
                ->assertSee('data-theme="light"', false)
                ->assertSee('theme-light.css', false)
                ->assertSee('data-site-theme-switch', false);
        }

        $this->withSession([Theme::COOKIE => 'light'])
            ->get('/cartao')
            ->assertOk()
            ->assertSee('data-theme="light"', false)
            ->assertSee('cartao-brand', false);
    }
}
