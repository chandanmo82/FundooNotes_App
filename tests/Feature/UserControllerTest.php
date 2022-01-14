<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    /**
     *Successfull registration 
     *@test
     */
    public function test_SuccessfulRegistration()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',])
        ->json('POST', '/api/auth/register', [
            "firstname" => "chandan",
            "lastname" => "kumar",
            "email" => "chandan12567800@gmail.com",
            "password" => "chandan@123",
            "confirm_password" => "chandan@123"
        ]);
        $response->assertStatus(201)->assertJson(['message' => 'User successfully registered']);
    }

    /**
     * @test for
     * Already Registered User
     */
    public function test_If_User_Already_Registered()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',])
        ->json('POST', '/api/auth/register', [
            "firstname" => "chandan",
            "lastname" => "kumar",
            "email" => "chandan1256@gmail.com",
            "password" => "chandan@123",
            "confirm_password" => "chandan@123"
        ]);
        $response->assertStatus(401)->assertJson(['message' => 'The email has already been taken']);
    }

    /**
     * @test for
     * Successfull login
     */

    public function test_SuccessfulLogin()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/auth/login', 
        [
            "email" => "chandan1256@gmail.com",
            "password" => "chandan@123"
        ]);
        $response->assertStatus(200)->assertJson(['message' => 'Login successfull']);
    }

    /**
     * @test for
     * Unsuccessfull Login
     */

    public function test_UnSuccessfulLogin()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
        ])->json('POST', '/api/auth/login', 
        [
            "email" => "chandan99999999@gmail.com",
            "password" => "chandan@123"
        ]);
        $response->assertStatus(401)->assertJson(['message' => 'we can not find the user with that e-mail address You need to register first']);
    }
    /**
     * @test for
     * Successfull Logout
     */
    public function test_SuccessfulLogout()
    {
        $response = $this->withHeaders([
            'Content-Type' => 'Application/json',
            'Authorization' => 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTY0MTg3NDkxNSwiZXhwIjoxNjQxODc4NTE1LCJuYmYiOjE2NDE4NzQ5MTUsImp0aSI6Im9iQ3FQVUJNRDJqWjU3RlgiLCJzdWIiOjEzLCJwcnYiOiIyM2JkNWM4OTQ5ZjYwMGFkYjM5ZTcwMWM0MDA4NzJkYjdhNTk3NmY3In0.prg4TCsRpkLMXTCI1yEqFy9GTvp99lrBy0AgRKQiKVY'
        ])->json('POST', '/api/auth/logout');
        $response->assertStatus(201)->assertJson(['message'=> 'User successfully signed out']);    
    }

}
