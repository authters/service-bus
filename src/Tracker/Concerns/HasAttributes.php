<?php

namespace Authters\ServiceBus\Tracker\Concerns;

trait HasAttributes
{
    /**
     * @var array|\ArrayAccess
     */
    protected $attributes;

    public function set(string $key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        if ($this->has($key)) {
            return $this->attributes[$key];
        }

        return $default;
    }

    public function has(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    public function mergeAttributes($attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
    }

    public function all()
    {
        return $this->attributes;
    }
}