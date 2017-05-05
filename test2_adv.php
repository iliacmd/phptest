<?php

/**
 * Задача: преобразовать входящий массив $data1 из одного формата в другой, в обе стороны
 */

class Test2{

    private $strategy;

    public function __construct(Strategy $strategy){
        $this->strategy = $strategy;
    }

    public function serialize(array $data){
        return $this->getStrategy()->serialize($data);
    }

    public function unserialize(array $data){
        return $this->getStrategy()->unserialize($data);
    }

    public function getStrategy(){
        return $this->strategy;
    }

}

class Strategy {

    public function __construct(){
    }

    public function serialize(array $data){
        return $this->serializeWalker($data);
    }

    public function unserialize(array $data){
        $result = [];
        foreach($data as $key => $value){
            $parts  = explode(".", $key . ".$value" );
            $result = array_merge_recursive($result, $this->unserializeWalker($parts));
        }
        return $result;
    }

    private function unserializeWalker(array $source, $prev = null){
        if(empty($source)){
            return $prev;
        }
        $value = array_pop($source);
        if(is_null($prev)){
            $next = $value;
        }else{
            $next[$value] = $prev;
        }
        return $this->unserializeWalker($source, $next);
    }

    private function serializeWalker(array $source, array $keys = []){
        $result = [];
        foreach($source as $key => $value){
            $keys = array_merge($keys, [$key]);
            if(is_array($value)){
                $result = array_merge($result, $this->serializeWalker($value, $keys));
                array_pop($keys);
            }else{
                $serialized_key = implode(".", $keys);
                $result[$serialized_key] = $value;
                array_pop($keys);
            }
        }
        return $result;
    }

}

class Printer{

    private $title;

    public function htmlPrint($data){
        echo "<div>{$this->getHead()}<div>";
        echo "<div>{$this->getBody($data)}</div>";
    }

    public function withTitle($title){
        $this->title = $title;
        return $this;
    }

    private function getHead(){
        return "<h3>{$this->title}</h3>";
    }

    private function getBody($data){
        return "<pre>{$this->getDumpArray($data)}</pre>";
    }

    private function getDumpArray($data){
        return print_r($data, true);
    }

}


$data1 = [
    'parent.child.field' => 1,
    'parent.child.field2' => 2,
    'parent2.child.name' => 'test',
    'parent2.child2.name' => 'test',
    'parent2.child2.position' => 10,
    'parent3.child3.position' => 10,
];

$test2 = new Test2(new Strategy());
$unserialized = $test2->unserialize($data1);
$serialized   = $test2->serialize($unserialized);

$printer = new Printer();
$printer->withTitle('Исходный массив')->htmlPrint($data1);
$printer->withTitle('Преобразуем туда')->htmlPrint($unserialized);
$printer->withTitle('Преобразуем обратно')->htmlPrint($serialized);
