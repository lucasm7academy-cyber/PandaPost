<?php

declare(strict_types=1);

use App\Models\Account;
use App\Models\Plan;
use App\Models\User;

test('admin index requires authentication', function () {
    $response = $this->get(route('app.admin.index'));

    $response->assertRedirect(route('login'));
});

test('non-admin authenticated users are forbidden from admin index', function () {
    $account = Account::factory()->create();
    $user = User::factory()->create([
        'account_id' => $account->id,
        'email' => 'regular@user.com',
    ]);

    $response = $this->actingAs($user)->get(route('app.admin.index'));

    $response->assertForbidden();
});

test('admin user can access admin index', function () {
    $account = Account::factory()->create();
    $admin = User::factory()->create([
        'account_id' => $account->id,
        'email' => 'lucasm7academy@gmail.com',
    ]);

    $response = $this->actingAs($admin)->get(route('app.admin.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/Index')
        ->has('accounts')
        ->has('plans')
    );
});

test('admin user can add trial days to an account', function () {
    $account = Account::factory()->create([
        'trial_ends_at' => null,
    ]);
    $admin = User::factory()->create([
        'account_id' => $account->id,
        'email' => 'lucasm7academy@gmail.com',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('app.admin.add-trial-days', $account), [
            'days' => 10,
        ]);

    $response->assertRedirect();
    expect($account->fresh()->trial_ends_at)->not->toBeNull();
    expect($account->fresh()->trial_ends_at->isFuture())->toBeTrue();
});

test('admin user can change plan of an account', function () {
    $account = Account::factory()->create();
    $plan = Plan::first();
    $admin = User::factory()->create([
        'account_id' => $account->id,
        'email' => 'lucasm7academy@gmail.com',
    ]);

    $response = $this->actingAs($admin)
        ->post(route('app.admin.change-plan', $account), [
            'plan_id' => $plan->id,
        ]);

    $response->assertRedirect();
    expect($account->fresh()->plan_id)->toBe($plan->id);
});
