<?php

namespace CornyPhoenix\Component\Glossary\Definition;

use CornyPhoenix\Component\Glossary\Glossary;

/**
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary\Definition
 */
final class ReferenceDefinition extends Definition
{

    const IDENTIFIER = '=>';
    const SYMBOL = '\ding{222}~';

    /**
     * @var string
     */
    private $references;

    /**
     * Definition constructor.
     * @param Glossary $glossary
     * @param string $name
     * @param string $references
     */
    public function __construct(Glossary $glossary, $name, $references) {
        parent::__construct($glossary, $name);
        $this->references = $references;
    }

    /**
     * @return string
     */
    public function getReferences() {
        return $this->references;
    }

    /**
     * {@inheritDoc}
     */
    public function getLaTeX(): string {
        $ref = $this->getReferences();

        return sprintf('\textit{\seename} \glslink{%s}{%s\textbf{%s}}', self::escape($ref), self::SYMBOL, $ref);
    }

    /**
     * @return string
     */
    public function getMarkdown(): string {
        return sprintf('_See_ %s', $this->getReference()->getMarkdownLink());
    }

    /**
     * @return string
     */
    public function getHtml(): string {
        return sprintf('<p class="reference">See_ %s</p>', $this->getReference()->getMarkdownLink());
    }

    /**
     * @return string
     */
    public function toString(): string {
        return '';
    }

    /**
     * @return string
     */
    public function getPrefix(): string {
        return self::IDENTIFIER.' '.$this->references;
    }

    /**
     * @return Definition
     */
    public function getReference(): string {
        return $this->getGlossary()->getDefinition($this->references);
    }

    /**
     * @return string
     */
    public function getMarkdownLink(): string {
        return sprintf('[%s](%s)', $this->getName(), $this->getReference()->getEscapedName());
    }

    /**
     * @return string
     */
    public function getHtmlLink(): string {
        return sprintf('<a href="%s.html">%s</a>', $this->getReference()->getEscapedName(), $this->getName());
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return false;
    }
}
