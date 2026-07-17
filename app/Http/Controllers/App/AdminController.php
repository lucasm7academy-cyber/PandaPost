<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Models\Account;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    public function index(): Response
    {
        $accounts = [];
        foreach (Account::with(['owner', 'plan'])->get() as $account) {
            $accounts[] = [
                'id' => $account->id,
                'name' => $account->name,
                'owner' => $account->owner ? [
                    'id' => $account->owner->id,
                    'name' => $account->owner->name,
                    'email' => $account->owner->email,
                ] : null,
                'plan' => $account->plan ? [
                    'id' => $account->plan->id,
                    'name' => $account->plan->name,
                    'slug' => $account->plan->slug->value,
                ] : null,
                'trial_ends_at' => $account->trial_ends_at ? $account->trial_ends_at->toIso8601String() : null,
                'is_on_trial' => $account->isOnTrial(),
                'usage' => $account->usage(),
            ];
        }

        $plans = [];
        foreach (Plan::orderBy('sort')->get() as $plan) {
            $plans[] = [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug->value,
            ];
        }

        return Inertia::render('admin/Index', [
            'accounts' => $accounts,
            'plans' => $plans,
        ]);
    }

    public function addTrialDays(Request $request, Account $account): RedirectResponse
    {
        $days = (int) $request->input('days', 7);

        $currentTrialEnds = $account->trial_ends_at && $account->trial_ends_at->isFuture()
            ? $account->trial_ends_at
            : now();

        $account->update([
            'trial_ends_at' => $currentTrialEnds->addDays($days),
        ]);

        return redirect()->back()->with('success', "Added {$days} trial days.");
    }

    public function changePlan(Request $request, Account $account): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        $account->update([
            'plan_id' => $request->plan_id,
        ]);

        $account->forgetPlanFeatureCache();

        return redirect()->back()->with('success', 'Plan updated successfully.');
    }
}
