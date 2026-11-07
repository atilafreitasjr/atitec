<?php

namespace Tests\Feature;

use App\Models\User;
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
}
