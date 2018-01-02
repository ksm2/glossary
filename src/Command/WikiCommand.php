<?php

namespace CornyPhoenix\Component\Glossary\Command;

use CornyPhoenix\Component\Glossary\Generator\WikiGenerator;
use CornyPhoenix\Component\Glossary\Glossary;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WikiCommand created on 01.01.18.
 *
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary\Command
 */
class WikiCommand extends Command
{

    protected function configure() {
        $this->setName('wiki');
        $this->setDescription('Generates a wiki from a glossary file');
        $this->setHelp('Generates a wiki');
        $this->addArgument('filename', InputArgument::REQUIRED, 'The input glossary\'s filename');
        $this->addOption('destination', 'd', InputOption::VALUE_REQUIRED, 'The destinated directory to output the wiki');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $filename = $input->getArgument('filename');
        $dest = $input->getOption('destination');

        $glossary = new Glossary();
        $glossary->addGenerator(new WikiGenerator($dest));
        $glossary->setFilename($filename);
    }
}
