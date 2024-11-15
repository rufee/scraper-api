<?php

namespace App\Models;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class Task
{
    /**
     * Object's id
     *
     * @var mixed|string
     */
    public mixed $id;

    /**
     * Prefix for Redis key store
     *
     * @var string
     */
    protected string $prefix = 'task';

    /**
     * Object's attributes
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->id = $attributes['id'] ?? Str::ulid();
    }

    /**
     * Serializes and sets object's attributes in Redis
     * Returns true if set
     *
     * @return bool
     */
    public function save() : bool
    {
        return Redis::set($this->getKey(), serialize($this->attributes));
    }

    /**
     * Retrieves and unserializes set attributes from Redis
     *
     * @param $id
     * @return static|null
     */
    public static function find($id): null|static
    {
        $key = (new static)->prefix . ':' . $id;
        $data = Redis::get($key);

        return $data ? new static(unserialize($data)) : null;
    }

    /**
     * Deletes key from Redis
     *
     * @return void
     */
    public function delete(): void
    {
        Redis::del($this->getKey());
    }

    /**
     * Get attributes via magic method
     *
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Set attributes via magic method
     *
     * @param $name
     * @param $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Returns full prefixed key for Redis get/set methods
     *
     * @return string
     */
    protected function getKey(): string
    {
        return $this->prefix . ':' . $this->id;
    }
}
