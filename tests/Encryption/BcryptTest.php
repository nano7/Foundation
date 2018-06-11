<?php namespace Tests\Encryption;

use Tests\TestCase;
use Nano7\Foundation\Encryption\BcryptHasher;

class BcryptTest extends TestCase
{
    /**
     * @var BcryptHasher
     */
    protected static $hasher;

    /**
     * Testar estrutura.
     */
    public function testEncryptClass()
    {
        $this->assertInstanceOf('Nano7\Foundation\Encryption\BcryptHasher', static::$hasher);
    }

    /**
     * Test criptografia.
     */
    public function testEncryptString()
    {
        $value = 'essa eh a string que sera criptografada';

        $hashed = static::$hasher->make($value);

        $this->assertTrue(static::$hasher->check($value, $hashed));
    }

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function setUpBeforeClass()
    {
        static::$hasher = new BcryptHasher([]);
    }

    /**
     * This method is called before the first test of this test class is run.
     */
    public static function tearDownAfterClass()
    {
        static::$hasher = null;
    }
}