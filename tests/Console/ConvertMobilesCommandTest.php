<?php

use Northstar\Models\User;

class ConvertMobilesCommandTest extends TestCase
{
    /** @test */
    public function it_should_convert_numbers()
    {
        $this->createMongoDocument('users', ['mobile' => '7455559417']);
        $this->createMongoDocument('users', ['mobile' => '6965552100']);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164');

        $this->seeInDatabase('users', ['mobile' => '7455559417', 'e164' => '+17455559417']);
        $this->seeInDatabase('users', ['mobile' => '6965552100', 'e164' => '+16965552100']);
    }

    /** @test */
    public function it_should_handle_invalid_mobiles()
    {
        $this->createMongoDocument('users', ['mobile' => '3']);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164');

        $user = User::first();
        $this->assertEquals(null, $user->e164);
        $this->assertNotNull($user->email);
    }

    /** @test */
    public function it_should_ignore_users_without_mobiles()
    {
        $user = factory(User::class)->create(['mobile' => null]);

        // Run the command to convert to E.164 format.
        $this->artisan('northstar:e164');

        $this->assertEquals(null, $user->fresh()->e164);
    }
}
