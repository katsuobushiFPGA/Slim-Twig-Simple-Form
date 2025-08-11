<?php
namespace Tests\Validators;

use PHPUnit\Framework\TestCase;
use App\Validators\ContactFormValidator;

class ContactFormValidatorTest extends TestCase
{
    private ContactFormValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ContactFormValidator();
    }

    public function testValidDataReturnsNoErrors(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => 'これは有効なテストメッセージです。'
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertEmpty($errors);
    }

    public function testEmptyNameReturnsError(): void
    {
        $data = [
            'name' => '',
            'email' => 'test@example.com',
            'message' => 'テストメッセージです。'
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertContains('お名前を入力してください。', $errors);
    }

    public function testLongNameReturnsError(): void
    {
        $data = [
            'name' => str_repeat('あ', 51), // 51文字
            'email' => 'test@example.com',
            'message' => 'テストメッセージです。'
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertContains('お名前は50文字以内で入力してください。', $errors);
    }

    public function testEmptyEmailReturnsError(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => '',
            'message' => 'テストメッセージです。'
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertContains('メールアドレスを入力してください。', $errors);
    }

    public function testInvalidEmailReturnsError(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'invalid-email',
            'message' => 'テストメッセージです。'
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertContains('正しいメールアドレスを入力してください。', $errors);
    }

    public function testLongEmailReturnsError(): void
    {
        // 254文字を超える長いメールアドレスを作成
        $longEmail = str_repeat('a', 246) . '@test.com'; // 255文字
        $data = [
            'name' => 'テスト太郎',
            'email' => $longEmail,
            'message' => 'テストメッセージです。'
        ];
        
        $errors = $this->validator->validate($data);
        
        // デバッグ用
        $this->assertNotEmpty($errors, 'エラーが発生していません。実際のエラー: ' . implode(', ', $errors));
        
        // 無効なメールアドレスエラーまたは長さエラーのいずれかが発生することを確認
        $hasEmailError = false;
        foreach ($errors as $error) {
            if (strpos($error, 'メールアドレス') !== false) {
                $hasEmailError = true;
                break;
            }
        }
        
        $this->assertTrue($hasEmailError, 'メールアドレス関連のエラーが発生していません。実際のエラー: ' . implode(', ', $errors));
    }

    public function testEmptyMessageReturnsError(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => ''
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertContains('お問い合わせ内容を入力してください。', $errors);
    }

    public function testShortMessageReturnsError(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => '短い' // 9文字以下
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertContains('お問い合わせ内容は10文字以上で入力してください。', $errors);
    }

    public function testLongMessageReturnsError(): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'message' => str_repeat('あ', 1001) // 1000文字超過
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertContains('お問い合わせ内容は1000文字以内で入力してください。', $errors);
    }

    public function testMultipleErrorsReturnsAllErrors(): void
    {
        $data = [
            'name' => '',
            'email' => 'invalid-email',
            'message' => ''
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertContains('お名前を入力してください。', $errors);
        $this->assertContains('正しいメールアドレスを入力してください。', $errors);
        $this->assertContains('お問い合わせ内容を入力してください。', $errors);
        $this->assertCount(3, $errors);
    }

    public function testTrimDataRemovesWhitespace(): void
    {
        $data = [
            'name' => '  テスト太郎  ',
            'email' => '  test@example.com  ',
            'message' => '  テストメッセージです。  '
        ];
        
        $trimmedData = $this->validator->getTrimmedData($data);
        
        $this->assertEquals('テスト太郎', $trimmedData['name']);
        $this->assertEquals('test@example.com', $trimmedData['email']);
        $this->assertEquals('テストメッセージです。', $trimmedData['message']);
    }

    public function testWhitespaceOnlyDataTreatedAsEmpty(): void
    {
        $data = [
            'name' => '   ',
            'email' => '   ',
            'message' => '   '
        ];
        
        $errors = $this->validator->validate($data);
        
        $this->assertContains('お名前を入力してください。', $errors);
        $this->assertContains('メールアドレスを入力してください。', $errors);
        $this->assertContains('お問い合わせ内容を入力してください。', $errors);
    }

    /**
     * @dataProvider validEmailProvider
     */
    public function testValidEmailFormats(string $email): void
    {
        $data = [
            'name' => 'テスト太郎',
            'email' => $email,
            'message' => 'テストメッセージです。'
        ];
        
        $errors = $this->validator->validate($data);
        
        // メールアドレス関連のエラーが含まれていないことを確認
        $emailErrors = array_filter($errors, function($error) {
            return strpos($error, 'メールアドレス') !== false;
        });
        
        $this->assertEmpty($emailErrors);
    }

    /**
     * @return array<int, array<string>>
     */
    public static function validEmailProvider(): array
    {
        return [
            ['test@example.com'],
            ['user.name@domain.co.jp'],
            ['user+tag@example.org'],
            ['test123@sub.domain.com'],
            ['simple@example.net'],
        ];
    }
}
