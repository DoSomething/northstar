<?php

use Illuminate\Database\Seeder;
use Northstar\Models\Client;
use Northstar\Auth\Scope;

class ClientTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('clients')->delete();

        // For easy testing, we'll seed one client for web authentication:
        factory(Client::class, 'authorization_code')->create([
            'title' => 'Local Development',
            'description' => 'This is an example web OAuth client seeded with your local Northstar installation.',
            'client_id' => 'oauth-test-client',
            'client_secret' => 'secret1',
            'scope' => collect(Scope::all())->except('admin')->keys()->toArray(),
            // @NOTE: We're omitting 'redirect_uri' here for easy local dev.
            'redirect_uri' => null,
        ]);

        // ..and one for machine authentication:
        factory(Client::class, 'client_credentials')->create([
            'title' => 'Local Development (Machine)',
            'description' => 'This is an example machine OAuth client seeded with your local Northstar installation.',
            'client_id' => 'machine-test-client',
            'client_secret' => 'secret2',
            'scope' => collect(Scope::all())->keys()->toArray(),
        ]);
    }
}
