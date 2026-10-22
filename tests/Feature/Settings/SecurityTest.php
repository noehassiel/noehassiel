<?php

use App\Livewire\Settings\Security;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passkeys\Passkey;
use Livewire\Livewire;
use PragmaRX\Google2FA\Google2FA;

uses(RefreshDatabase::class);

test('security page requires password confirmation', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('settings.security'))
        ->assertRedirect(route('password.confirm'));
});

test('security page can be rendered once the password is confirmed', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('settings.security'))
        ->assertOk()
        ->assertSee('Activar 2FA')
        ->assertSee('Aún no tienes passkeys');
});

test('the old password settings url redirects to security', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/settings/password')
        ->assertRedirect('/settings/security');
});

test('two factor authentication can be enabled and confirmed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test(Security::class)
        ->call('startTwoFactorSetup')
        ->assertNotSet('qrCodeSvg', '')
        ->assertDispatched('open-modal', id: 'two-factor-setup-modal');

    $code = app(Google2FA::class)->getCurrentOtp(decrypt($user->fresh()->two_factor_secret));

    $component
        ->call('showVerification')
        ->set('code', $code)
        ->call('confirmTwoFactor')
        ->assertHasNoErrors()
        ->assertSet('twoFactorEnabled', true)
        ->assertSet('qrCodeSvg', '');

    expect($user->fresh()->two_factor_confirmed_at)->not->toBeNull()
        ->and($component->get('recoveryCodes'))->toHaveCount(8);
});

test('two factor authentication is not confirmed with a wrong code', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Security::class)
        ->call('startTwoFactorSetup')
        ->set('code', '000000')
        ->call('confirmTwoFactor')
        ->assertHasErrors('code')
        ->assertSet('twoFactorEnabled', false);

    expect($user->fresh()->two_factor_confirmed_at)->toBeNull();
});

test('an abandoned two factor setup is discarded on the next visit', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $this->actingAs($user);

    Livewire::test(Security::class)->assertSet('twoFactorEnabled', false);

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('two factor authentication can be disabled', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    Livewire::test(Security::class)
        ->assertSet('twoFactorEnabled', true)
        ->call('disableTwoFactor')
        ->assertSet('twoFactorEnabled', false);

    expect($user->fresh()->two_factor_secret)->toBeNull();
});

test('recovery codes can be regenerated', function () {
    $user = User::factory()->withTwoFactor()->create();

    $this->actingAs($user);

    Livewire::test(Security::class)
        ->assertSet('recoveryCodes', ['recovery-code-one', 'recovery-code-two'])
        ->call('regenerateRecoveryCodes')
        ->assertNotSet('recoveryCodes', ['recovery-code-one', 'recovery-code-two']);
});

test('passkeys can be listed and removed', function () {
    $user = User::factory()->create();
    $passkey = createPasskeyFor($user, 'MacBook Pro');

    $this->actingAs($user);

    Livewire::test(Security::class)
        ->assertSee('MacBook Pro')
        ->call('confirmDeletePasskey', $passkey->id)
        ->assertSet('deletingPasskeyName', 'MacBook Pro')
        ->call('deletePasskey')
        ->assertSet('passkeys', []);

    expect(Passkey::query()->count())->toBe(0);
});

test('a passkey belonging to someone else can not be removed', function () {
    $passkey = createPasskeyFor(User::factory()->create(), 'Not mine');

    $this->actingAs(User::factory()->create());

    expect(fn () => Livewire::test(Security::class)->call('confirmDeletePasskey', $passkey->id))
        ->toThrow(ModelNotFoundException::class);

    expect(Passkey::query()->count())->toBe(1);
});

/**
 * Store a passkey row directly; registering a real one needs a browser authenticator.
 */
function createPasskeyFor(User $user, string $name): Passkey
{
    return $user->passkeys()->forceCreate([
        'name' => $name,
        'credential_id' => fake()->uuid(),
        'credential' => [],
    ]);
}
