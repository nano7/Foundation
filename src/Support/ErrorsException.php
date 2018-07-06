<?php namespace Nano7\Foundation\Support;

use Illuminate\Support\MessageBag;

class ErrorsException extends \Exception
{
    /**
     * @var array
     */
    protected $errors = [];

    /**
     * @param string $message
     * @param null $code
     * @param \Exception|null $previous
     * @param array $errors
     */
    public function __construct($message, $code = null, $previous = null, array $errors = [])
    {
        parent::__construct($message, $code, $previous);

        $this->errors = $errors;
    }

    /**
     * @param null $key
     * @return array|null
     */
    public function getErrors($key = null)
    {
        if (is_null($key)) {
            return $this->errors;
        }

        return array_key_exists($key, $this->errors) ? $this->errors[$key] : null;
    }

    /**
     * @return MessageBag
     */
    public function toMessageBag()
    {
        $bag = new MessageBag($this->errors);
        if ($this->getMessage() != '') {
            $bag->add('__message', $this->getMessage());
        }

        return $bag;
    }

    /**
     * @param array $errors
     * @param string $message
     * @return ErrorsException
     */
    public static function withMessages(array $errors, $message = '')
    {
        return new ErrorsException($message, null, null, $errors);
    }
}