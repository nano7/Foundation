<?php namespace Nano7\Foundation\Support;

class StrNameValue
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param $name
     * @param null $default
     * @return null|string|mixed
     */
    public function get($name, $default = null)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : $default;
    }

    /**
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * @param $filename
     * @return bool
     * @throws \Exception
     */
    public function load($filename)
    {
        if (!file_exists($filename)) {
            throw new \Exception("File [$filename] not found");
        }

        return $this->setText(file_get_contents($filename));
    }

    /**
     * @param $filename
     * @return bool
     */
    public function save($filename)
    {
        file_put_contents($filename, $this->getText());

        return true;
    }

    /**
     * @return string
     */
    public function getText()
    {
        $text = '';
        foreach ($this->data as $n => $v) {
            $text .= ($text != '') ? "\r\n" : '';
            $text .= is_null($v) ? $n : sprintf('%s=%s', $n, $v);
        }

        return $text;
    }

    /**
     * @param $text
     * @return bool
     */
    public function setText($text)
    {
        $this->data = [];

        // Tratar quebras de linhas
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);

        // Separar linha no array
        $lines = explode("\n", trim($text));

        foreach ($lines as $line) {
            if (preg_match('/([a-zA-Z0-9_-]+)+=(.*)+/', $line, $args)) {
                $name  = $args[1];
                $value = $args[2];

                $this->data[$name] = $value;
            } else {
                $this->data[] = $line;
            }
        }

        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        return array_key_exists($name, $this->data);
    }

    /**
     * @param $name
     * @return null|string|mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getText();
    }
}