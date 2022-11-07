<?php
namespace Facebook\PersistentData;
use Facebook\PersistentData\PersistentDataInterface;

class SessionPersistentDataHandler implements PersistentDataInterface
{
    public function get($key)
    {
        return session($key);
    }

    public function set($key, $value)
    {
        session($key, $value);
    }
}
