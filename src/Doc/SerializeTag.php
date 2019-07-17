<?php

namespace Netlic\XmlSerializer\Doc;

use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\DocBlock\Tags\Factory\StaticMethod;
use phpDocumentor\Reflection\Types\Context;
use Webmozart\Assert\Assert;

/**
 * Class SerializeTag
 * @package Netlic\XmlSerializer\Doc
 * @method string getNsName()
 * @method string getMethodName()
 */
class SerializeTag extends BaseTag implements StaticMethod
{

    /** @var array */
    private $initArray = [];

    /** @var string */
    private $nsName;
    /** @var string */
    private $methodName;

    protected $name = 'xmlserialize';

    public static function create($body, DescriptionFactory $descriptionFactory = null, Context $context = null): SerializeTag
    {
        preg_match('/\((.*)\)/', $body, $matches);
        $setup = end($matches);
        $initArray = [];
        foreach (explode(';', $setup) as $setting) {
            $tagAttributeInitParam = explode('=', $setting);
            $initArray[reset($tagAttributeInitParam)] = trim(end($tagAttributeInitParam), '[]');
        }

        return new static($initArray);
    }

    public function __construct(array $initArray)
    {
        $this->initArray = $initArray;
    }

    public function __call($name, $arguments)
    {
        $keyString = str_replace("get", "", $name);
        $lcString = lcfirst($keyString);
        if ($this->isPropertySet($lcString)) {
            return $this->$lcString;
        }
        $keyParts = preg_split('/(?=[A-Z])/', $keyString, -1, PREG_SPLIT_NO_EMPTY);
        $firstKey = strtolower(reset($keyParts));
        $lastKey = strtolower(end($keyParts));
        if (!empty($this->initArray[$firstKey])) {
            $value = $this->initArray[$firstKey];
            $pattern = sprintf('/%s:"(.*)\"/', $lastKey);
            preg_match($pattern, $value, $matches);
            if (empty($matches)) {
                return null;
            }
            $this->$lcString = end($matches);

            return $this->$lcString;
        }

        return null;
    }

    private function isPropertySet(string $property)
    {
        return property_exists(self::class, $property) && $this->$property;
    }

    public function __toString()
    {
        return (string)$this->description;
    }
}