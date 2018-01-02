<?php

namespace CornyPhoenix\Component\Glossary\Command;

use CornyPhoenix\Component\Glossary\Generator\WebsiteGenerator;
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
class WebsiteCommand extends Command
{

    protected function configure() {
        $defaultTemplate = dirname(__DIR__).DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'template.phtml';
        $this->setName('website');
        $this->setDescription('Generates a website from a glossary file');
        $this->setHelp('Generates a website');
        $this->addArgument('filename', InputArgument::REQUIRED, 'The input glossary\'s filename');
        $this->addOption('destination', 'd', InputOption::VALUE_REQUIRED, 'The destinated directory to output the wiki');
        $this->addOption('template', 't', InputOption::VALUE_OPTIONAL, 'The template for the HTML documents', $defaultTemplate);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $filename = $input->getArgument('filename');
        $dest = $input->getOption('destination');
        $templateFilename = $input->getOption('template');

        $glossary = new Glossary();
        $glossary->addGenerator(new WebsiteGenerator($dest, $templateFilename));
        $glossary->setFilename($filename);
    }
}
