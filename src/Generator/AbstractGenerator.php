<?php

namespace CornyPhoenix\Component\Glossary\Generator;

use CornyPhoenix\Component\Glossary\Definition\Definition;
use CornyPhoenix\Component\Glossary\Glossary;

/**
 * Class AbstractGenerator created on 02.01.18.
 *
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary\Generator
 */
abstract class AbstractGenerator implements GeneratorInterface
{

    /**
     * @var string
     */
    protected $fileExtension;

    /**
     * @var string
     */
    protected $directory;


    /**
     * Wiki constructor.
     * @param string $fileExtension
     * @param string $directory
     */
    public function __construct(string $fileExtension, string $directory) {
        $this->fileExtension = $fileExtension;
        $this->directory = $directory;
    }

    /**
     * @return string
     */
    public function getFileExtension(): string {
        return $this->fileExtension;
    }

    /**
     * @return string
     */
    public function getDirectory(): string {
        return $this->directory;
    }

    /**
     * @param Glossary $glossary
     * @param array $refs
     * @param Definition $def
     * @param Definition $last
     * @param Definition $next
     */
    protected function writeEntry(
        Glossary $glossary,
        array $refs,
        Definition $def,
        Definition $last = null,
        Definition $next = null
    ) {
        $name = $def->getEscapedName();

        $handle = fopen($this->buildFilename($name), 'w');
        fwrite($handle, $this->buildEntry($glossary, $refs, $def, $last, $next));
        fclose($handle);

        $this->copyImages($def);
    }

    /**
     * @param string $relative
     * @param string $extension
     * @return string
     */
    protected function buildFilename(string $relative, string $extension = '') {
        return $this->directory.DIRECTORY_SEPARATOR.$relative.'.'.($extension ?: $this->fileExtension);
    }

    protected abstract function buildEntry(
        Glossary $glossary,
        array $refs,
        Definition $def,
        Definition $last = null,
        Definition $next = null
    ): string;

    /**
     * @param Definition[] $definitions
     * @return Definition[][]
     */
    protected function groupDefinitionsByLetter(array $definitions) {
        return array_reduce(
            $definitions,
            function (array $group, Definition $definition) {
                $letter = $definition->getLetter();
                if (!isset($group[$letter])) {
                    $group[$letter] = [$definition];
                } else {
                    $group[$letter][] = $definition;
                }

                return $group;
            },
            []
        );
    }

    /**
     * Copies images of a definition to the wiki.
     *
     * @param Definition $definition
     */
    protected function copyImages(Definition $definition): void {
        $destDir = $this->directory.'/img/';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }
        foreach ($definition->getImages() as $image) {
            copy($image, $destDir.basename($image));
        }
    }

    /**
     * Copies all images to the wiki.
     */
    protected function emptyImages(): void {
        $dir = $this->directory.'/img/';
        if (!is_dir($dir)) {
            return;
        }

        $imageDir = opendir($dir);
        while (false !== ($entry = readdir($imageDir))) {
            if ('.' === $entry[0]) {
                continue;
            }

            unlink($dir.$entry);
        }
        closedir($imageDir);
    }
}
