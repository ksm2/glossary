<?php

namespace CornyPhoenix\Component\Glossary\Definition;

use CornyPhoenix\Component\Glossary\Glossary;

/**
 * Class Definition.
 *
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary\Definition
 */
abstract class Definition
{

    /**
     * @var string
     */
    private $name;

    /**
     * @var string[]
     */
    private $tags;

    /**
     * @var string[]
     */
    private $images;

    /**
     * @var Glossary
     */
    private $glossary;

    /**
     * Definition constructor.
     * @param Glossary $glossary
     * @param string $name
     */
    public function __construct(Glossary $glossary, string $name) {
        $this->name = $name;
        $this->tags = [];
        $this->images = [];
        $this->glossary = $glossary;
    }

    /**
     * @param string $subject
     * @return string
     */
    protected static function escape(string $subject): string {
        $map = [
            'ä' => 'ae',
            'ö' => 'oe',
            'ü' => 'ue',
            'Ä' => 'Ae',
            'Ö' => 'Oe',
            'Ü' => 'Ue',
            'ß' => 'ss',
        ];
        foreach ($map as $search => $replace) {
            $subject = str_replace($search, $replace, $subject);
        }
        $subject = strtolower($subject);

        return trim(preg_replace('#[^0-9a-z]+#', '-', $subject), '-');
    }

    /**
     * @return Glossary
     */
    public function getGlossary(): Glossary {
        return $this->glossary;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getTags(): array {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     * @return $this
     */
    public function setTags(array $tags): self {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param string $tag
     * @return $this
     */
    public function addTag(string $tag): self {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getImages(): array {
        return $this->images;
    }

    /**
     * @param string[] $images
     * @return $this
     */
    public function setImages(array $images): self {
        $this->images = $images;

        return $this;
    }

    /**
     * @param string $image
     * @return $this
     */
    public function addImage(string $image): self {
        $this->images[] = $image;

        return $this;
    }

    /**
     * @return string
     */
    public function getEscapedName(): string {
        return self::escape($this->name);
    }

    /**
     * @return string
     */
    public abstract function getLaTeX(): string;

    /**
     * @return string
     */
    public abstract function getMarkdown(): string;

    /**
     * @return string
     */
    public abstract function getHtml(): string;

    /**
     * @return string
     */
    public abstract function toString(): string;

    /**
     * @return string
     */
    public function getMarkdownLink(): string {
        return sprintf('[%s](%s)', $this->name, $this->getEscapedName());
    }

    /**
     * @return string
     */
    public function getHtmlLink(): string {
        return sprintf('<a href="%s.html">%s</a>', $this->getEscapedName(), $this->name);
    }

    /**
     * @return string
     */
    public function getPrefix(): string {
        return '';
    }

    /**
     * @return bool
     */
    public abstract function isEmpty(): bool;
}
