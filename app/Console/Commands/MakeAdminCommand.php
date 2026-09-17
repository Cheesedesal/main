<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdminCommand extends Command
{
    protected $signature = 'user:make-admin {email}';
    protected $description = 'Promote an existing user to administrator';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (! $user) {
            $this->error('No user found with that email.');

            return self::FAILURE;
        }
        $user->update(['role' => 'admin']);
        $this->info("{$user->email} is now an administrator.");

        return self::SUCCESS;
    }
}