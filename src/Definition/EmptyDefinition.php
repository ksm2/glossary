<?php

namespace CornyPhoenix\Component\Glossary\Definition;

/**
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary\Definition
 */
final class EmptyDefinition extends Definition
{

    /**
     * @return string
     */
    public function toString(): string {
        return '';
    }

    /**
     * @return string
     */
    public function getMarkdown(): string {
        return sprintf('_There is no content for %s yet!_', $this->getName());
    }

    /**
     * @return string
     */
    public function getHtml(): string {
        return sprintf('<p class="empty">There is no content for %s yet!</p>', $this->getName());
    }

    /**
     * @return string
     */
    public function getLaTeX(): string {
        return '\ding{55}';
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool {
        return true;
    }
}
