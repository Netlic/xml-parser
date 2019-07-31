<?php

namespace Netlic\XmlSerializer;

use Netlic\XmlSerializer\Doc\SerializeTag;
use Netlic\XmlSerializer\Helper\XmlTag;
use phpDocumentor\Reflection\DocBlockFactory;

class XmlSerializer
{
    private const PARSER_COMMENT = 'xmlserialize';

    /** @var DocBlockFactory */
    private $docBlockFactory;

    public function __construct(DocBlockFactory $docBlockFactory = null)
    {
        $this->docBlockFactory = $docBlockFactory ?? DocBlockFactory::createInstance(['xmlserialize' => SerializeTag::class]);
    }

    public function canDeserialize(): bool
    {

    }

    public function deserialize(string $xml)
    {
        new \SimpleXMLElement($xml);
    }

    /**
     * @param object $object
     * @param boolean $stripClassNamespaces
     * @return string
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function serialize(object $object, $stripClassNamespaces = true): string
    {
        $reflectedObject = new \ReflectionClass($object);
        $className = $stripClassNamespaces ? $this->stripClassNamespaces($reflectedObject->getName()) : $reflectedObject->getName();

        $objectTag = new XmlTag($className);
        //$objectTag->addNamespaces(['test' => 'test5']);
        foreach ($reflectedObject->getProperties() as $property) {
            $phpDoc = $this->serializeWithDoc($property);

            if ($property->isPublic()) {
                $xmlTag = (new XmlTag($property->getName(), $property->getValue($object)))->addPrefixedNamespace($phpDoc['ns']['name']);
                $objectTag->append($xmlTag);

                continue;
            }

            $method = 'get' . ucfirst($property->getName());
            if ($reflectedObject->hasMethod($method)) {
                $methodToInvoke = $reflectedObject->getMethod($method);
                if ($methodToInvoke->isPublic()) {
                    $xmlTag = (new XmlTag($property->getName(), $methodToInvoke->invoke($object)))->addPrefixedNamespace($phpDoc['ns']['name']);
                    $objectTag->append($xmlTag);
                }

                continue;
            }

            if (strlen($phpDoc['method']['name']) > 0) {
                $method = trim($phpDoc['method']['name']);
                if ($reflectedObject->hasMethod($method) && $reflectedObject->getMethod($method)->isPublic()) {
                    $methodToInvoke = $reflectedObject->getMethod($method);
                    $xmlTag = (new XmlTag($property->getName(), $methodToInvoke->invoke($object)))->addPrefixedNamespace($phpDoc['ns']['name']);
                    $objectTag->append($xmlTag);
                }

                continue;
            }
        }

        return (string)$objectTag;
    }

    /**
     * @param \ReflectionProperty $property
     * @return array
     * @throws \Exception
     */
    private function serializeWithDoc(\ReflectionProperty $property): array
    {
        $arr = [
            'ns' => [
                'name' => ''
            ],
            'method' => [
                'name' => ''
            ]
        ];

        if ((bool)$property->getDocComment()) {
            $docBlock = $this->docBlockFactory->create($property->getDocComment());

            /** @var SerializeTag[] $tagsFound */
            $tagsFound = $docBlock->getTagsByName('xmlserialize');
            if (count($tagsFound) > 1) {
                throw new \Exception(sprintf('Error while parsing @%s: Only one tag row possible', self::PARSER_COMMENT), 500);
            }

            if (!empty($tagsFound)) {
                $description = reset($tagsFound);
                $arr['ns']['name'] = (string)$description->getNsName();
                $arr['method']['name'] = (string)$description->getMethodName();
            }
        }

        return $arr;
    }

    /**
     * @param string $className
     * @return string
     */
    private function stripClassNamespaces($className)
    {
        if (strpos($className, '\\') !== false) {
            $classNameArray = explode('\\', $className);
            $className = end($classNameArray);
        }

        return $className;
    }
}