<?php
namespace Source;

class Product{
    private int $id;
    private string $name;
    private float $price;
    private int $quantity;

    public function __construct(int $id=0, string $name="",float $price=0, int $quantity=1){
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function getId(){
        return $this->id;
    }

    public function setId(int $newId){
        $this->id = $newId;
    }

    public function getName(){
        return $this->name;
    }

    public function setName(string $newName){
        $this->name = $newName;
    }

    public function getPrice(){
        return $this->price;
    }

    public function setPrice(int $newPrice){
        $this->price = $newPrice;
    }

    public function getQuantity(){
        return $this->quantity;
    }

    public function setQuantity(int $newQuantity){
        $this->quantity = $newQuantity;
    }

    public function toArray(){
        return ['id' => $this->getId(), 'name' => $this->getName(), 'price' => $this->getPrice(), 'quantity' => $this->getQuantity()];
    }

}