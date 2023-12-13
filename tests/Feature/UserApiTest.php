<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $seeder = new DatabaseSeeder();
        $seeder->run();
    }

    public function test_can_register_user(): void
    {
        $userToCreate = [
            'email' => 'testuser@gmail.com',
            'name' => 'testuser',
            'password' => 'test123'
        ];
        $response = $this->post("/api/register", $userToCreate, ['Accept' => 'application/json']);
        $response->assertStatus(201);

        unset($userToCreate['password']); //Unset as PW is now Hashed in DB

        $this->assertDatabaseHas('users', $userToCreate);
    }

    public function test_cant_register_user_with_duplicate_email(): void
    {
        $userToCreate = [
            'email' => 'testuser2@gmail.com',
            'name' => 'testuser2',
            'password' => 'test123'
        ];
        $response = $this->post("/api/register", $userToCreate, ['Accept' => 'application/json']);
        $response->assertStatus(201);
        $response = $this->post("/api/register", $userToCreate, ['Accept' => 'application/json']);
        $response->assertStatus(422);
    }

    public function test_can_login_user(): void
    {
        $userToCreate = [
            'email' => 'testuser3@gmail.com',
            'name' => 'testuser3',
            'password' => 'test123'
        ];
        $response = $this->post("/api/register", $userToCreate, ['Accept' => 'application/json']);
        $response->assertStatus(201);

        $userToLoginWith = [
            'email' => 'testuser3@gmail.com',
            'password' => 'test123'
        ];
        $response = $this->post("/api/login", $userToLoginWith, ['Accept' => 'application/json']);
        $response->assertStatus(200);
    }

    public function test_cant_login_user_with_invalid_details(): void
    {
        $userToCreate = [
            'email' => 'testuser4@gmail.com',
            'name' => 'testuser4',
            'password' => 'test123'
        ];
        $response = $this->post("/api/register", $userToCreate, ['Accept' => 'application/json']);
        $response->assertStatus(201);

        $userToLoginWith = [
            'email' => 'testuser4@gmail.com',
            'password' => 'test1234'
        ];
        $response = $this->post("/api/login", $userToLoginWith, ['Accept' => 'application/json']);
        $response->assertStatus(401);
    }
}
