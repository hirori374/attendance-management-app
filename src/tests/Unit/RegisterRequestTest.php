<?php

namespace Tests\Unit;

use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class RegisterRequestTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    
    public function testValidDataPasses()
    {
        $request = new RegisterRequest();

        $data = [
            'name' => 'Taro',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );

        $this->assertFalse($validator->fails());
    }
    public function testNameIsRequired()
    {
        $request = new RegisterRequest();

        $data = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $validator = Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            ['お名前を入力してください'],
            $validator->errors()->get('name')
        );
    }
    public function testEmailIsRequired()
    {
        $request = new RegisterRequest();

        $data = [
            'name' => 'Taro',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
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
        $request = new RegisterRequest();

        $data = [
            'name' => 'Taro',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
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
    public function testPasswordIsMin()
    {
        $request = new RegisterRequest();

        $data = [
            'name' => 'Taro',
            'email' => 'test@example.com',
            'password' => 'test123',
            'password_confirmation' => 'test123',
        ];

        $validator = Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            ['パスワードは8文字以上で入力してください'],
            $validator->errors()->get('password')
        );
    }
    public function testPasswordIsConfirmed()
    {
        $request = new RegisterRequest();

        $data = [
            'name' => 'Taro',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password111',
        ];

        $validator = Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertEquals(
            ['パスワードと一致しません'],
            $validator->errors()->get('password')
        );
    }
}
