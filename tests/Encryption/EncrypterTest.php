<?php namespace Tests\Encryption;

use Tests\TestCase;
use Nano7\Foundation\Encryption\Encrypter;

class EncrypterTest extends TestCase
{
    /**
     * @var Encrypter
     */
    protected static $encrypter;

    /**
     * Testar estrutura.
     */
    public function testEncryptClass()
    {
        $this->assertInstanceOf('Nano7\Foundation\Encryption\Encrypter', static::$encrypter);
        $this->assertEquals('isso eh um teste de 32 chars ok?', static::$encrypter->getKey());
    }

    /**
     * Teste de keys suportados
     */
    public function testSupportes()
    {
        $this->assertTrue(static::$encrypter->supported('1234567890123456',                    'AES-128-CBC')); // 16 chars
        $this->assertTrue(static::$encrypter->supported('12345678901234567890123456789012',    'AES-256-CBC')); // 32 chars

        $this->assertFalse(static::$encrypter->supported('1234567890123456',                    'XXX-128-CBC')); // 16 chars
        $this->assertFalse(static::$encrypter->supported('12345678901234567890123456789012',    'XXX-256-CBC')); // 32 chars

        $this->assertFalse(static::$encrypter->supported('1234567890',                         'AES-128-CBC')); // 10 chars
        $this->assertFalse(static::$encrypter->supported('1234567890123456789',                'AES-128-CBC')); // 19 chars
        $this->assertFalse(static::$encrypter->supported('123456789012345678901234567890',     'AES-256-CBC')); // 30 chars
        $this->assertFalse(static::$encrypter->supported('1234567890123456789012345678901234', 'AES-256-CBC')); // 34 chars

        $this->assertTrue(static::$encrypter->supported('isso eh um teste',                    'AES-128-CBC')); // 16 chars
        $this->assertTrue(static::$encrypter->supported('isso eh um teste de 32 chars ok?',    'AES-256-CBC')); // 32 chars
    }

    /**
     * Testando o generate key.
     */
    public function testGenerateKey()
    {
        $this->assertEquals(16, strlen(static::$encrypter->generateKey('AES-128-CBC')));
        $this->assertEquals(32, strlen(static::$encrypter->generateKey('AES-256-CBC')));

        $this->assertEquals(null, static::$encrypter->generateKey('XXX-256-CBC'));
    }

    /**
     * Test criptografia.
     */
    public function testEncryptString()
    {
        $value = 'essa eh a string que sera criptografada';

        $this->assertEquals($value, static::$encrypter->decryptString(static::$encrypter->encryptString($value)));
    }

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        $key = 'isso eh um teste de 32 chars ok?';

        static::$encrypter = new Encrypter($key, 'AES-256-CBC');
    }

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        static::$encrypter = null;
    }
}