<?php

namespace Tests\Unit;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testValidDataPasses()
    {
        $request = new LoginRequest();

        $data = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $validator = Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );

        $this->assertFalse($validator->fails());
    }
    public function testEmailIsRequired()
    {
        $request = new LoginRequest();

        $data = [
            'email' => '',
            'password' => 'password123',
        ];

        $validator = Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            ['メールアドレスを入力してください'],
            $validator->errors()->get('email')
        );
    }
    public function testPasswordIsRequired()
    {
        $request = new LoginRequest();

        $data = [
            'email' => 'test@example.com',
            'password' => '',
        ];

        $validator = Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            ['パスワードを入力してください'],
            $validator->errors()->get('password')
        );
    }
}
