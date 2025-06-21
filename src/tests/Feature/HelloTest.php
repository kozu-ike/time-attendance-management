<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelloTest extends TestCase
{
    use RefreshDatabase;

    public function test_hello()
    {

        User::factory()->create([
            'name' => 'aaa',
            'email' => 'bbb@ccc.com',
            'password' => 'test12345',
        ]);
        $this->assertDatabaseHas('users', [
            'name' => 'aaa',
            'email' => 'bbb@ccc.com',
            'password' => 'test12345',
        ]);
    }
}
