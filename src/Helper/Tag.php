<?php

namespace Netlic\XmlSerializer\Helper;

class Tag
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $text = '';

    /** @var boolean */
    protected $pairTag;

    /** @var Tag[] */
    protected $children = [];

    /** @var array */
    protected $attributes = [];

    public function __construct(string $name, $text = '', $pair = true)
    {
        $this->name = $name;
        $this->text = $text;
        $this->pairTag = $pair;
    }

    /**
     * Appends tag to parent tag
     * @param Tag $tag
     * @return Tag
     * @throws \Exception
     */
    public function append(Tag $tag): Tag
    {
        if (!$this->pairTag) {
            throw new \Exception('Cannot append to non-pair tag', 500);
        }
        $this->children[] = $tag;

        return $this;
    }

    /**
     * @param string|array $attr
     * @return Tag|string
     */
    public function attr($attr)
    {
        if (is_array($attr)) {
            foreach ($attr as $attrName => $attrValue) {
                $this->attributes[] = sprintf('%s="%s"', $attrName, (string)$attrValue);
            }

            return $this;
        }

        if (!empty($this->attributes[$attr])) {
            return $this->attributes[$attr];
        }

        return $this;
    }

    /**
     * Returns this particular tags children
     * @return Tag[]
     */
    public function children()
    {
        return $this->children;
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Adds or return text
     * @param string $text
     * @return Tag|string
     */
    public function text(string $text = null): Tag
    {
        if ($text) {
            $this->text = $text;

            return $this;
        }

        return $this->text;
    }

    /**
     * Renders tag
     * @return string
     */
    protected function render(): string
    {
        $attributes = sprintf(" %s ", implode(" ", $this->attributes));
        if (trim($attributes) === '') {
            $attributes = '';
        }
        $renderedXml = sprintf('<%s%s/>', $this->name, $attributes);
        if ($this->pairTag) {
            $childrenRenderedXml = '';
            foreach ($this->children as $child) {
                $childrenRenderedXml .= $child->render();
            }
            $pairRenderedXml = str_replace('/', '', $renderedXml);
            $renderedXml = sprintf('%s%s%s%s', $pairRenderedXml, $this->text, $childrenRenderedXml, sprintf('</%s>', $this->name));
        }

        return $renderedXml;
    }
}