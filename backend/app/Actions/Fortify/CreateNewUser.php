<?php

namespace App\Actions\Fortify;

use App\Models\Entry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(User::class),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = new User([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                'content_scrambler_algorithm' => rand(0, User::MAX_SCRAMBLE_ALGORITHM_VALUE),
            ]);
            $user->save();

            if ($user->name == 'admin') {
                // This is only okay as long as we use `Rule::unique` to `name`,
                // otherwise everyone would call themselves `admin`.
                $user->roles()->syncWithoutDetaching(Role::where('name', Role::ADMIN)->first()->id);
            }

            Entry::createForModel($user);
            return $user;
        });
    }
}
