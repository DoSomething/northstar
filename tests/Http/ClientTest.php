<?php

use Northstar\Models\Client;

class ClientTest extends TestCase
{
    /**
     * Verify a non-admin user is not able to list clients.
     */
    public function testIndexAsNormalUser()
    {
        $this->asNormalUser()->get('v2/clients');
        $this->assertResponseStatus(401);
    }

    /**
     * Verify an admin user is able to list all clients.
     */
    public function testIndexAsAdminUser()
    {
        Client::create(['client_id' => 'test']);
        Client::create(['client_id' => 'testingz']);

        $this->asAdminUser()->get('v2/clients');
        $this->assertResponseStatus(200);
        $this->seeJsonStructure([
            'data' => [
                '*' => [
                    'client_id', 'client_secret', 'scope', 'allowed_grant', 'redirect_uri',
                ],
            ],
        ]);
    }

    /**
     * Verify a non-admin user is not able to create new clients.
     */
    public function testStoreAsNormalUser()
    {
        $this->asNormalUser()->json('POST', 'v2/clients', [
            'title' => 'Dog',
            'description' => 'hello this is doge',
            'client_id' => 'dog',
            'scope' => ['admin'],
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Verify an admin is able to create a new client.
     */
    public function testStoreAsAdminUser()
    {
        $this->asAdminUser()->json('POST', 'v2/clients', [
            'title' => 'Dog',
            'description' => 'hello this is doge',
            'client_id' => 'dog',
            'allowed_grant' => 'client_credentials',
            'scope' => ['admin'],
        ]);

        $this->assertResponseStatus(201);
        $this->seeJsonStructure([
            'data' => [
                'client_id', 'client_secret', 'scope',
            ],
        ]);
    }

    /**
     * Verify a non-admin user is not able to see whether a client exists or not.
     */
    public function testShowWontExposeClientNames()
    {
        $this->asNormalUser()->get('v2/clients/notarealkey');
        $this->assertResponseStatus(401);
    }

    /**
     * Verify a non-admin user is not able to see client details.
     */
    public function testShowAsNormalUser()
    {
        $client = Client::create(['client_id' => 'phpunit_key']);

        $this->asNormalUser()->get('v2/clients/'.$client->client_id);
        $this->assertResponseStatus(401);
    }

    /**
     * Verify a admin user is able to see client details.
     */
    public function testShowAsAdminUser()
    {
        $client = Client::create(['client_id' => 'phpunit_key']);

        $this->asAdminUser()->get('v2/clients/'.$client->client_id);
        $this->assertResponseStatus(200);
    }

    /**
     * Verify a non-admin user is not able to update clients.
     */
    public function testUpdateAsNormalUser()
    {
        $client = Client::create(['client_id' => 'update_key']);

        $this->asNormalUser()->json('PUT', 'v2/clients/'.$client->client_id, [
            'scope' => [
                'admin',
                'user',
            ],
        ]);

        $this->assertResponseStatus(401);
    }

    /**
     * Verify an admin is able to update a client.
     */
    public function testUpdateAsAdminUser()
    {
        $client = Client::create(['client_id' => 'update_key', 'allowed_grant' => 'password']);

        $this->asAdminUser()->json('PUT', 'v2/clients/'.$client->client_id, [
            'title' => 'New Title',
            'scope' => ['admin', 'user'],
            'allowed_grant' => 'authorization_code',
            'redirect_uri' => 'http://example.com/callback',
        ]);

        $this->assertResponseStatus(200);
        $this->seeInDatabase('clients', [
            'title' => 'New Title',
            'client_id' => 'update_key',
            'scope' => ['admin', 'user'],
            'allowed_grant' => 'authorization_code',
            'redirect_uri' => 'http://example.com/callback',
        ]);
    }

    /**
     * Verify a non-admin user is not able to delete clients.
     * @test
     */
    public function testDestroyAsNormalUser()
    {
        $client = Client::create(['client_id' => 'delete_me']);

        $this->asNormalUser()->json('DELETE', 'v2/clients/'.$client->client_id);
        $this->assertResponseStatus(401);

        // It's still there!
        $this->seeInDatabase('clients', ['client_id' => 'delete_me']);
    }

    /**
     * Verify an admin is able to delete a client.
     * @test
     */
    public function testDestroyAsAdminUser()
    {
        $client = Client::create(['client_id' => 'delete_me']);

        $this->asAdminUser()->json('DELETE', 'v2/clients/'.$client->client_id);
        $this->assertResponseStatus(200);

        $this->dontSeeInDatabase('clients', ['client_id' => 'delete_me']);
    }
}
