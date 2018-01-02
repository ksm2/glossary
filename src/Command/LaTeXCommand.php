<?php

namespace CornyPhoenix\Component\Glossary\Command;

use CornyPhoenix\Component\Glossary\Generator\LaTeXGenerator;
use CornyPhoenix\Component\Glossary\Glossary;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LaTeXCommand created on 01.01.18.
 *
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary\Command
 */
class LaTeXCommand extends Command
{

    protected function configure() {
        $this->setName('latex');
        $this->setDescription('Generates LaTeX from a glossary file');
        $this->setHelp('Generates LaTeX');
        $this->addArgument('filename', InputArgument::REQUIRED, 'The input glossary\'s filename');
        $this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'The output LaTeX file', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $filename = $input->getArgument('filename');
        $out = $input->getOption('output');

        $glossary = new Glossary();
        $generator = new LaTeXGenerator($out);
        $glossary->addGenerator($generator);
        $glossary->setFilename($filename);
        if (null === $out) {
            $output->write($generator->buildLaTeXString($glossary));
        }
    }
}
