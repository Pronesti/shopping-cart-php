<?php

namespace Source;
use Error;

class Cart
{
    private array $shoppingCart = [];

    public function __construct()
    {
    }

    public function showList()
    {
        return json_encode(array_map(function ($product) {
            return $product->toArray();
        }, $this->shoppingCart));
    }

    public function addProduct($newItem)
    {
        $this->shoppingCart[] = $newItem;
    }

    public function removeProduct(string $key, $value)
    {
        $removed = count($this->shoppingCart);
        $this->shoppingCart = array_filter($this->shoppingCart, function ($item) use ($key, $value, $removed) {
            $getter = "get" . ucfirst($key);
            if (method_exists($item, $item->$getter())) {
                throw new Error('Parameter not found(' . $getter . ')');
            }
            return ($item->$getter() != $value);
        });
        $removed = $removed - count($this->shoppingCart);
        return $removed;
    }

    public function editProduct(string $originalKey, $originalValue, $key, $value)
    {
        foreach ($this->shoppingCart as $k => $product) {
            $getter = "get" . ucfirst($originalKey);
            if (method_exists($product, $product->$getter())) {
                throw new Error('Getter not found(' . $getter . ')');
            }
            
            if ($product->$getter() == $originalValue) {
                $setter = "set" . ucfirst($key);
                if (method_exists($product, $product->$setter($value))) {
                    throw new Error('Setter not found(' . $setter . ')');
                }
                $product->$setter($value);
            }
        }
        return true;
    }
}
