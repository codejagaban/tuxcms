<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;

/**
 * Creates an editor account. This replaces public self-registration, which has
 * no place in a single-site CMS.
 */
class CreateUser extends Command
{
    protected $signature = 'user:create
                            {--name= : The user\'s display name}
                            {--email= : The user\'s email address}';

    protected $description = 'Create a CMS user account';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('Email');
        $password = $this->secret('Password (min 8 characters)');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => ['required', Password::min(8)],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Created user {$email}.");

        return self::SUCCESS;
    }
}
