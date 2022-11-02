<?php

namespace Tests\Feature\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_user()
    {
        //arrange
        $request = [
            'email' => 'admin@example.com',
            'password' => 'teste'
        ];

        //act
        $response = $this->post('/api/auth/login', $request);

        //assert
        $response->assertSuccessful();
    }

    public function test_create_user_successfully()
    {
        //arrange
        $request = [
            'name' => 'Felipe Bisol',
            'email' => 'felipebisol01@gmail.com',
            'password' => 'teste',
            'is_admin' => false
        ];

        $user = User::query()->where('is_admin', '=', true)->first();

        //act
        $response = $this->actingAs($user)->post('/api/auth/register', $request);

        //assert
        $response->assertSuccessful();
        $this->assertDatabaseHas('users', [
            'name' => $request['name'],
            'email' => $request['email']
        ]);
        $this->assertTrue(\Hash::check($request['password'], $user->password));
    }

    public function test_create_user_unsuccessfully_because_of_the_gate()
    {
        //arrange
        $request = [
            'name' => 'Felipe Bisol',
            'email' => 'felipebisol01@gmail.com',
            'password' => 'teste',
            'is_admin' => false
        ];

        //act
        $user = User::query()->where('is_admin', '=', false)->first();
        $response = $this->actingAs($user)->post('/api/auth/register', $request);

        //assert
        $response->assertUnauthorized();
        $this->assertDatabaseMissing('users', [
            'name' => $request['name'],
            'email' => $request['email']
        ]);
        $this->assertTrue(\Hash::check($request['password'], $user->password));
    }
}