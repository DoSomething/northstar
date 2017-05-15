<?php

use Northstar\Models\User;

class MergeTest extends TestCase
{
    /**
     * Test that anonymous and normal users can't merge accounts.
     * POST /users/:id/merge
     *
     * @test
     */
    public function testResetNotAccessibleByNonAdmin()
    {
        $user = factory(User::class)->create();

        $this->post('v1/users/'.$user->id.'/merge');
        $this->assertResponseStatus(403);

        $this->asNormalUser()->post('v1/users/'.$user->id.'/merge');
        $this->assertResponseStatus(401);
    }

    /**
     * Test merging some accounts.
     * POST /resets
     *
     * @test
     */
    public function testMergingAccounts()
    {
        $user = User::forceCreate([
            'email' => 'target-account@example.com',
            'first_name' => 'Phil',
            'last_name' => 'Dunfy',
            'addr_street1' => '19 W 21st St',
            'city' => 'New York',
            'addr_state' => 'NY',
            'addr_zip' => '10010',
            'country' => 'USA',
            'drupal_id' => '1234567',
            'source' => 'phoenix',
        ]);

        $duplicate = User::forceCreate([
            'mobile' => '5551234567',
            'mobilecommons_id' => '199483623',
            'mobilecommons_status' => 'active',
            'drupal_id' => '7175144',
            'source' => 'sms',
        ]);

        $this->asAdminUser()->json('POST', 'v1/users/'.$user->id.'/merge', [
            'id' => $duplicate->id,
        ]);

        // The "target" user should have the dupe's profile fields.
        $this->seeInDatabase('users', [
            '_id' => $user->id,
            'email' => $user->email,
            'mobile' => $duplicate->mobile,
            'mobilecommons_id' => $duplicate->mobilecommons_id,
            'mobilecommons_status' => $duplicate->mobilecommons_status,
            'drupal_id' => '1234567',
        ]);

        // The "duplicate" user should have the duplicate fields removed.
        $this->seeInDatabase('users', [
            '_id' => $duplicate->id,
            'email' => 'merged-account-'.$user->id.'@dosomething.invalid',
            'mobile' => null,
            'mobilecommons_id' => null,
            'mobilecommons_status' => null,
            'drupal_id' => '7175144',
        ]);
    }
}
