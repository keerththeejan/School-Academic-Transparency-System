<?php

namespace App\Http\Controllers;

use App\Support\PasswordRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'locale' => ['required', 'in:en,ta,si'],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', PasswordRules::make()],
        ]);

        $user->fill([
            'name' => $data['name'],
            'mobile' => $data['mobile'] ?? null,
            'locale' => $data['locale'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        if ($user->parentProfile) {
            $user->parentProfile->forceFill([
                'preferred_language' => $data['locale'],
                'full_name' => $data['name'],
                'mobile' => $data['mobile'] ?? $user->parentProfile->mobile,
            ])->save();
        }

        return back()->with('status', __('ui.saved'));
    }
}
