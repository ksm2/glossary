<?php

namespace CornyPhoenix\Component\Glossary\Generator;

use CornyPhoenix\Component\Glossary\Glossary;

/**
 * Interface GeneratorInterface
 *
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary\Generator
 */
interface GeneratorInterface
{

    /**
     * Generates the glossary output from a model.
     *
     * @param Glossary $glossary The model to generate the output from.
     */
    public function generate(Glossary $glossary);
}
