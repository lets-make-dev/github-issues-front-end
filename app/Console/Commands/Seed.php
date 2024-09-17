<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Console\Command;

class Seed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        Project::create([
            'name' => 'My Awesome Project',
        ]);

        // create a user and team
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'm@ke.dev',
            'password' => bcrypt('password'),
        ]);

        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));

    }
}
