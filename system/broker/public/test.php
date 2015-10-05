<?php

class A {

    public $a;

    public function set($obj) {
        $obj->a = 5;
    }

}

$obj = new A();
$obj->a = 6;

$obj->set($obj);

echo serialize(array('section' => 'countries', 'q' => 1));