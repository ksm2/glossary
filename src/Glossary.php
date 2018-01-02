<?php

namespace CornyPhoenix\Component\Glossary;

use CornyPhoenix\Component\Glossary\Definition\BodyDefinition;
use CornyPhoenix\Component\Glossary\Definition\Definition;
use CornyPhoenix\Component\Glossary\Definition\EmptyDefinition;
use CornyPhoenix\Component\Glossary\Definition\ReferenceDefinition;
use CornyPhoenix\Component\Glossary\Generator\GeneratorInterface;

/**
 * Class Glossary.
 *
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary
 */
class Glossary
{

    const TAG_IDENTIFIER = '#';
    const IMAGE_IDENTIFIER = '!';

    /**
     * @var string
     */
    private $filename;

    /**
     * @var null|Definition[]
     */
    private $definitions;

    /**
     * @var string[]
     */
    private $tags = [];

    /**
     * @var string[]
     */
    private $meta;

    /**
     * @var GeneratorInterface[]
     */
    private $generators;

    /**
     * Glossary constructor.
     * @param GeneratorInterface[] $generators
     */
    public function __construct(array $generators = []) {
        $this->meta = [];
        $this->generators = [];
    }

    /**
     * @param string $text
     */
    public static function warn(string $text) {
        error_log("\e[1;33mWARN:\e[m $text");
    }

    /**
     * @param string $text
     */
    public static function err(string $text) {
        error_log("\e[1;31mERR:\e[m $text");
    }

    /**
     * @param GeneratorInterface $generator
     * @return self
     */
    public function addGenerator(GeneratorInterface $generator): self {
        $this->generators[] = $generator;

        return $this;
    }

    /**
     * @param array $array
     * @param $prefix
     * @return string[]
     */
    private static function prefix(array $array, $prefix): array {
        return array_map(
            function ($image) use ($prefix) {
                return $prefix.$image;
            },
            $array
        );
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename) {
        if ($this->readOutDefinitions($filename)) {
            $this->filename = $filename;
            $this->writeOutDefinitions();
            foreach ($this->generators as $generator) {
                $generator->generate($this);
            }
        }

        return $this;
    }

    /**
     * @return Definition[]
     */
    public function getDefinitions(): array {
        return $this->definitions;
    }

    /**
     * @return string[]
     */
    public function getTags(): array {
        return $this->tags;
    }

    /**
     * @return array
     */
    public function buildReferenceMap(): array {
        $map = [];
        foreach ($this->definitions as $definition) {
            if ($definition instanceof BodyDefinition) {
                $definition->getParsedBody(
                    function (Definition $def) use ($definition, &$map) {
                        if (!isset($map[$def->getName()])) {
                            $map[$def->getName()] = [$definition->getName() => $definition];
                        } else {
                            $map[$def->getName()][$definition->getName()] = $definition;
                        }

                        return '';
                    }
                );
            }
        }

        return $map;
    }

    /**
     * @return string
     */
    public function __toString() {
        $line = '';
        $line .= $this->writeFrontMatter();

        $empty = [];
        foreach ($this->definitions as $definition) {
            if ($definition instanceof EmptyDefinition) {
                $empty[] = $definition;
                continue;
            }

            $line = $this->writeDefinition($line, $definition);
        }

        foreach ($empty as $definition) {
            $line = $this->writeDefinition($line, $definition);
        }

        return $line;
    }

    /**
     * Writes out definitions in file.
     */
    public function writeOutDefinitions() {
        $handle = fopen($this->filename, 'w');
        fwrite($handle, $this->__toString());
        fclose($handle);
    }

    /**
     * @param string $name
     * @return Definition
     */
    public function getDefinition($name) {
        if (!isset($this->definitions[$name])) {
            Glossary::warn("No definition for \e[1m$name\e[m.");

            return null;
        }

        return $this->definitions[$name];
    }

    /**
     * @return string[]
     */
    public function getAllMeta(): array {
        return $this->meta;
    }

    /**
     * @param string $string
     * @return null|string
     */
    public function getMeta(string $string): ?string {
        if (!isset($this->meta[$string])) {
            Glossary::warn("No meta entry for \e[1m$string\e[m.");

            return null;
        }

        return $this->meta[$string];
    }

    /**
     * @return Definition[][]
     */
    public function getTaggedDefinitions() {
        $map = [];
        foreach ($this->definitions as $definition) {
            foreach ($definition->getTags() as $tag) {
                if (!isset($map[$tag])) {
                    $map[$tag] = [];
                }
                $map[$tag][] = $definition;
            }
        }

        return $map;
    }

    /**
     * Reads glossary entries.
     * @param string $filename
     * @return bool
     */
    private function readOutDefinitions(string $filename) {
        $handle = fopen($filename, 'r');
        /** @var Definition|BodyDefinition $currentDef */
        $currentDef = null;
        $defs = [];
        $tags = [];
        while (($line = fgets($handle)) !== false) {
            if (preg_match('#^([^:]+):\s*(.*)$#', $line, $matches)) {
                list(, $key, $value) = $matches;
                $this->meta[$key] = $value;
                continue;
            }

            break;
        }

        while (($line = fgets($handle)) !== false) {
            // Match key line.
            if (preg_match('#^([^\s:][^:]*):(.*)$#', $line, $matches)) {
                // Write current Definition.
                if ($currentDef !== null) {
                    if ($currentDef instanceof BodyDefinition && $currentDef->isEmpty()) {
                        $defs[$currentDef->getName()] = $currentDef->makeEmpty();
                    } else {
                        $defs[$currentDef->getName()] = $currentDef;
                    }
                }

                // Match empty def.
                list(, $currentName, $rest) = $matches;

                if (isset($defs[$currentName])) {
                    self::err(sprintf("Duplicate entry: \e[1m%s\e[m", $currentName));

                    return false;
                }

                $currentDef = $this->createDef($currentName, $rest);
                $currentDef->setTags($this->readOutTags($rest));
                $currentDef->setImages($this->readOutImages($filename, $rest));

                $tags = array_merge($tags, $currentDef->getTags());

                continue;
            }

            // Append to current body def.
            if ($currentDef instanceof BodyDefinition) {
                $currentDef->appendBody($line);
                continue;
            }
        }

        // Write current definition.
        if ($currentDef !== null) {
            if ($currentDef instanceof BodyDefinition && $currentDef->isEmpty()) {
                $defs[$currentDef->getName()] = $currentDef->makeEmpty();
            } else {
                $defs[$currentDef->getName()] = $currentDef;
            }
        }

        fclose($handle);
        $this->definitions = $this->sortDefinitions($defs);
        $this->tags = $this->sortDefinitions(array_values(array_unique($tags)));
        $this->warnEmptyDefinitions();

        return true;
    }

    /**
     * Creates a new Definition from a name and a rest key line.
     *
     * @param $currentName
     * @param string $rest
     * @return Definition
     */
    private function createDef($currentName, $rest) {
        $trim = trim($rest);

        if (strpos($trim, ReferenceDefinition::IDENTIFIER) === 0) {
            return new ReferenceDefinition($this, $currentName, ltrim(substr($trim, 2)));
        }

        return new BodyDefinition($this, $currentName);
    }

    /**
     * @param string $string
     * @return string[]
     */
    private function readOutTags($string) {
        if (!preg_match_all(sprintf('/%s(\w+)/', self::TAG_IDENTIFIER), $string, $matches)) {
            return [];
        }

        return $matches[1];
    }

    /**
     * @param string $filename
     * @param string $string
     * @return string[]
     */
    private function readOutImages(string $filename, string $string): array {
        if (!preg_match_all('/'.self::IMAGE_IDENTIFIER.'([^\\s'.self::IMAGE_IDENTIFIER.']+)/', $string, $matches)) {
            return [];
        }

        return self::checkImagesExist($filename, $matches[1]);
    }

    /**
     * @param Definition $definition
     * @return string[]
     */
    private function formatTags(Definition $definition): array {
        return self::prefix($definition->getTags(), self::TAG_IDENTIFIER);
    }

    /**
     * @param Definition $definition
     * @return string[]
     */
    private function formatImages(Definition $definition): array {
        return self::prefix(array_keys($definition->getImages()), self::IMAGE_IDENTIFIER);
    }

    /**
     * Warns about empty definitions.
     */
    private function warnEmptyDefinitions() {
        foreach ($this->definitions as $definition) {
            if ($definition instanceof EmptyDefinition) {
                if (count($definition->getImages())) {
                    continue;
                }

                $entry = $definition->getName();
                self::warn("Entry \e[1m$entry\e[m is empty.");
            }
        }
    }

    /**
     * Filters out non existing images.
     *
     * @param string $filename
     * @param string[] $images
     * @return string[]
     */
    private function checkImagesExist(string $filename, array $images) {
        $srcDir = dirname($filename).'/';

        return array_filter(
            array_combine(
                $images,
                array_map(
                    function ($image) use ($srcDir) {
                        $path = realpath($srcDir.$image);
                        if (!file_exists($path)) {
                            self::warn("Image \e[1m$image\e[m is missing. Is it a PNG?");

                            return null;
                        }

                        return $path;
                    },
                    $images
                )
            ),
            'is_string'
        );
    }

    /**
     * @return string
     */
    private function writeFrontMatter() {
        $line = '';
        foreach ($this->meta as $key => $value) {
            $line .= $key;
            $line .= ': ';
            $line .= $value;
            $line .= "\n";
        }
        $line .= "---\n";

        return $line;
    }

    /**
     * Sorts given definitions.
     *
     * @param Definition[] $defs
     * @return Definition[]
     */
    private function sortDefinitions(array $defs) {
        uksort(
            $defs,
            function ($subject1, $subject2) {
                $subject1 = $this->escapeSort($subject1);
                $subject2 = $this->escapeSort($subject2);

                if ($subject1 < $subject2) {
                    return -1;
                }

                if ($subject1 > $subject2) {
                    return 1;
                }

                return 0;
            }
        );

        return $defs;
    }

    /**
     * @param string $line
     * @param Definition $definition
     * @return string
     */
    private function writeDefinition($line, Definition $definition) {
        $name = $definition->getName();
        $line .= $name.': ';
        $annotations = array_merge(
            $definition->getPrefix() ? [$definition->getPrefix()] : [],
            $this->formatTags($definition),
            $this->formatImages($definition)
        );
        $line .= implode(' ', $annotations);
        $line .= $definition->toString();
        $line .= "\n";

        return $line;
    }

    /**
     * @param string $subject
     * @return string
     */
    private function escapeSort(string $subject): string {
        $map = [
            'ä' => 'a',
            'ö' => 'o',
            'ü' => 'u',
            'Ä' => 'a',
            'Ö' => 'o',
            'Ü' => 'u',
            'ß' => 's',
        ];
        foreach ($map as $search => $replace) {
            $subject = str_replace($search, $replace, $subject);
        }
        $subject = strtolower($subject);

        return preg_replace('/[^0-9a-z]+/', '', $subject);
    }
}
