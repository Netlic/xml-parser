<?php

namespace Netlic\XmlSerializer\Helper;

class XmlTag extends Tag
{
    /**
     * @param array $namespaces
     * @return XmlTag
     */
    public function addNamespaces(array $namespaces): XmlTag
    {
        foreach ($namespaces as $name => $value) {
            $this->attr(['xlmns:' . $name => $value]);
        }

        return $this;
    }

    /**
     * @param string $namespace
     * @return XmlTag
     */
    public function addPrefixedNamespace(string $namespace = ''): XmlTag
    {
        if (strlen(trim($namespace)) > 0) {
            $this->name = $namespace . ':' . $this->name;
        }

        return $this;
    }
}