<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_signup(): void
    {
        $response = $this->postJson(
            '/api/signup',
            [
                'name' => 'Sally',
                'password' => 'password',
                'password_confirmation' => 'password',
                'email' => 'sallsy@gmail.com',
                'phone_number' => '081234567890',
            ]
        );

        $response
            ->assertStatus(201);
    }
    public function test_signin(): void
    {
        $response = $this->postJson(
            '/api/signin',
            [
                'password' => 'password',
                'email' => 'sallsy@gmail.com',
            ]
        );

        $response
            ->assertStatus(201);
    }

    public function test_login_and_get_token(): void
    {

        $response = $this->postJson('/api/signin', [
            'email' => 'sallsy@gmail.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'user' => [
                    'id',
                    'name',
                    'email',
                    // Add other user fields here
                ],
                'token',
            ]);
    }

    public function test_signout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/signout');

        $response->assertStatus(200);
    }
}
