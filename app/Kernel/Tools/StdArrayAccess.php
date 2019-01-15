<?php

namespace App\Kernel\Tools;

trait StdArrayAccess
{
    /**
     * array items
     */
    protected $items = array();

    protected function isInRange($index)
    {
        return is_int($index) && ($index < $this->count()) && ($index > -1);
    }

	public function all()
	{
		return $this->toArray();
	}

    public function toArray()
    {
        return $this->items;
    }

	public function toJson($options = 0, $depth = 512)
	{
		return json_encode($this->items, $options, $depth);
	}

    public function count()
    {
        return count($this->items);
    }
    
    public function clear()
    {
        $this->items = array();
    }
    
	public function merge($items)
	{
		return new static(array_merge($this->items, $this->getArrayableItems($items)));
	}
    
    public function __set($name, $value)
    {
        $this->put($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return Arr::has($this->items, $name);
    }

    public function __unset($name)
    {
        Arr::forget($this->items, $name);
    }
    
    public function get($key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }
    
    public function getInt($key, $default = null)
    {
        return intval($this->get($key, $default));
    }
    
    public function getFloat($key, $default = null)
    {
        return floatval($this->get($key, $default));
    }

    public function keyOf($value, $strict = false)
    {
        return array_search($value, $this->items, $strict);
    }

	public function has($key)
	{
		$keys = is_array($key) ? $key : func_get_args();
		return Arr::has($this->items, $keys);
	}

	public function keys()
	{
		return new static(array_keys($this->items));
	}

    public function add($item)
    {
        $this->items[] = $item;
        return $this;
    }
    
    public function forget($keys)
    {
        Arr::forget($this->items, $keys);
        return $this;
    }

    public function insert($key, $item)
    {
        if ($this->isInRange($key)) {
            $lst = array_splice($this->items, $key, 0, array($item));
            return is_array($lst);
        } elseif ($key < 0) {
            return $this->insert(0, $item);
        } elseif ($key >= $this->count()) {
            return $this->add($item);
        }
    }

    public function put($key, $item)
    {
        if (!is_null($key)){
            $this->items[$key] = $item;            
        }
    }
    
    public function first(callable $callback = null, $default = null)
    {
        return Arr::first($this->items, $callback, $default);
    }

	public function last(callable $callback = null, $default = null)
	{
		return Arr::last($this->items, $callback, $default);
	}

	public function implode($value, $glue = null)
	{
		$first = $this->first();

		if (is_array($first) || is_object($first)) {
			return implode($glue, $this->pluck($value)->all());
		}

		return implode($value, $this->items);
	}

    public function isEmpty()
    {
        return $this->count() == 0;
    }
    
    public function isNotEmpty()
    {
        return $this->count() > 0;
    }
    
    public function pull($key, $default = null)
    {
        $retVal = $this->get($key, $default);
		$this->forget($key);
        return $retVal;
    }
    
    public function prepend($value, $key = null)
    {
    	Arr::prepend($this->items, $value, $key);
    }
    
    public function extractFirst()
    {
        return array_shift($this->items);
    }
    
    public function pop()
    {
        return array_pop($this->items);
    }

	public function intersect($items)
	{
		return new static(array_intersect($this->items, $this->getArrayableItems($items)));
	}
    
    /**
     * Returns a new collection of filtered items
     * @param string|Callable $filter
     * @return static
     */
	public function where($key, $operator = null, $value = null)
	{
		return $this->filter($this->operatorForWhere(...func_get_args()));
	}
    
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }
        return $this;
    }

	public function contains($key, $operator = null, $value = null)
	{
		if (func_num_args() === 1) {
			if ($this->useAsCallable($key)) {
				$placeholder = new stdClass;

				return $this->first($key, $placeholder) !== $placeholder;
			}

			return in_array($key, $this->items);
		}

		return $this->contains($this->operatorForWhere(...func_get_args()));
	}

	public function containsStrict($key, $value = null)
	{
		if (func_num_args() === 2) {
			return $this->contains(function ($item) use ($key, $value) {
				return data_get($item, $key) === $value;
			});
		}

		if ($this->useAsCallable($key)) {
			return ! is_null($this->first($key));
		}

		return in_array($key, $this->items, true);
	}

	public function diffKeys($items)
	{
		return new static(array_diff_key($this->items, $this->getArrayableItems($items)));
	}

	public function diffAssoc($items)
	{
		return new static(array_diff_assoc($this->items, $this->getArrayableItems($items)));
	}

	public function diffAssocUsing($items, callable $callback)
	{
		return new static(array_diff_uassoc($this->items, $this->getArrayableItems($items), $callback));
	}

	public function diffKeysUsing($items, callable $callback)
	{
		return new static(array_diff_ukey($this->items, $this->getArrayableItems($items), $callback));
	}

	public function collapse()
	{
		return new static(Arr::collapse($this->items));
	}

	public function filter(callable $callback = null)
	{
		if ($callback) {
			return new static(Arr::where($this->items, $callback));
		}

		return new static(array_filter($this->items));
	}

	public function except($keys)
	{
		if ($keys instanceof self) {
			$keys = $keys->all();
		} elseif (! is_array($keys)) {
			$keys = func_get_args();
		}

		return new static(Arr::except($this->items, $keys));
	}


	protected function useAsCallable($value)
	{
		return ! is_string($value) && is_callable($value);
	}

	protected function getArrayableItems($items)
	{
		if (is_array($items)) {
			return $items;
		} elseif ($items instanceof static) {
			return $items->all();
		}

		return (array) $items;
	}

	protected function operatorForWhere($key, $operator = null, $value = null)
	{
		if (func_num_args() === 1) {
			$value = true;

			$operator = '=';
		}

		if (func_num_args() === 2) {
			$value = $operator;

			$operator = '=';
		}

		return function ($item) use ($key, $operator, $value) {
			$retrieved = data_get($item, $key);

			$strings = array_filter([$retrieved, $value], function ($value) {
				return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
			});

			if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) == 1) {
				return in_array($operator, ['!=', '<>', '!==']);
			}

			switch ($operator) {
				default:
				case '=':
				case '==':  return $retrieved == $value;
				case '!=':
				case '<>':  return $retrieved != $value;
				case '<':   return $retrieved < $value;
				case '>':   return $retrieved > $value;
				case '<=':  return $retrieved <= $value;
				case '>=':  return $retrieved >= $value;
				case '===': return $retrieved === $value;
				case '!==': return $retrieved !== $value;
			}
		};
	}
    
}