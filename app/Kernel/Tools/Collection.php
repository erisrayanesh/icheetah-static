<?php
namespace App\Kernel\Tools;

class Collection implements \ArrayAccess, \Iterator
{
    
    use StdArrayAccess;
    
    public function __construct($items = array())
    {
		$this->items = $this->getArrayableItems($items);
    }
    
    public static function make($items = array())
    {
        return new static($items);
    }
    
    //ArrayAccess
    
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    //Iterator
    
    public function current()
    {
        return current($this->items);
    }

    public function key()
    {
        return key($this->items);
    }

    public function next()
    {
        return next($this->items);
    }

    public function rewind()
    {
        reset($this->items);
    }

    public function valid()
    {
        $key = key($this->items);
        $var = ($key !== null && $key !== false);
        return $var;
    }
        
}

?>