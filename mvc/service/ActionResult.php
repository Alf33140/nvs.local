<?php

class ActionResult
{
    public bool $success;
    public string $css_class;
    public string $message;
    public array $data;

    public function __construct(bool $success, string $css_class, string $message = '', array $data = [])
    {
        $this->success = $success;
        $this->css_class = $css_class;
        $this->message = $message;
        $this->data = $data;
    }

    public static function success(string $message = '', array $data = []): self
    {
        return new self(true, 'success', $message, $data);
    }

    public static function fail(string $message = '', array $data = []): self
    {
        return new self(false, 'warning', $message, $data);
    }
}
