<?php

use Netlic\XmlSerializer\XmlSerializer;

require 'vendor/autoload.php';

class test{

    /**
     * @var string
     * @xmlserialize (ns=[name:"ahoj"])
     */
    public $test = 'blabla';
    private $test1 = 'nieco';

    /**
     * @var string
     * @xmlserialize (ns=[name:"funguj"];method=[name:"absurdMethod"])
     */
    private $test2 = 'absurdValue';

    public function getTest1()
    {
        return $this->test1;
    }

    public function absurdMethod()
    {
        return $this->test2;
    }
}

$parser = new XmlSerializer();

try {
    var_dump($parser->serialize(new test()));
} catch (ReflectionException $e) {
} catch (Exception $e) {
}