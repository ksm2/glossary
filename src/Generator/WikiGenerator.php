<?php

namespace CornyPhoenix\Component\Glossary\Generator;

use CornyPhoenix\Component\Glossary\Definition\Definition;
use CornyPhoenix\Component\Glossary\Glossary;

/**
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary\Generator
 */
class WikiGenerator implements GeneratorInterface
{

    /**
     * @var string
     */
    private $directory;

    /**
     * Wiki constructor.
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * Generates the wiki.
     *
     * @param Glossary $glossary
     */
    public function generate(Glossary $glossary)
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0777, true);
        }

        // Empties the image directory.
        $this->emptyImages();

        // Read current entries.
        $currentEntries = $this->readCurrentEntries();

        // Write out each entry.
        $abandonedEntries = $this->writeEntries($glossary, $currentEntries);

        // Delete abandoned entries.
        $this->deleteEntries($abandonedEntries);

        // Write the home page.
        $this->writeHomePage($glossary->getMeta('title'), $glossary->getDefinitions());

        // Write aggregated pages.
        $this->writeTagsPage($glossary->getTags());
        $this->writeSidebar($glossary->getTags());
        $this->writeFooter($glossary->getMeta('author'));
        $this->writeTags($glossary->getTaggedDefinitions());
    }

    /**
     * @return array
     */
    private function readCurrentEntries(): array
    {
        $entries = [];
        $dir = opendir($this->directory);
        if ($dir === false) return [];

        while (false !== ($entry = readdir($dir))) {
            if ('.' === $entry[0]) {
                continue;
            }
            if ('.md' !== substr($entry, -3)) {
                continue;
            }

            $entries[substr($entry, 0, -3)] = true;
        }

        closedir($dir);
        return $entries;
    }

    /**
     * @param Glossary $glossary
     * @param string[] $entries
     * @return string[]
     */
    private function writeEntries(Glossary $glossary, array $entries): array
    {
        // Build a reference map from the current glossary.
        $map = $glossary->buildReferenceMap();
        
        $current = null;
        $next = null;
        foreach ($glossary->getDefinitions() as $definition) {
            if (null === $next) {
                $next = $definition;
                continue;
            }

            /** @var Definition $last */
            $last = $current;
            /** @var Definition $current */
            $current = $next;
            /** @var Definition $next */
            $next = $definition;

            $refs = isset($map[$current->getName()]) ? $map[$current->getName()] : [];
            $entries = $this->writeEntry($entries, $refs, $current, $last, $next);
        }
        $refs = isset($map[$next->getName()]) ? $map[$next->getName()] : [];
        $entries = $this->writeEntry($entries, $refs, $next, $current);

        return $entries;
    }

    /**
     * @param resource $handle
     * @param Definition[] $refs
     * @param Definition $def
     * @param Definition|null $prev
     * @param Definition|null $next
     */
    private function writeOutWikiEntry($handle, array $refs, Definition $def, Definition $prev = null, Definition $next = null): void
    {
        fwrite($handle, '# ' . $def->getName());
        fwrite($handle, "\n");

        if (count($def->getTags())) {
            fwrite($handle, "> tagged with: ");
            $implode = implode(
                ', ',
                array_map(
                    function ($tag) {
                        return "[#$tag]($tag)";
                    },
                    $def->getTags()
                )
            );
            fwrite($handle, "$implode\n");
        }

        fwrite($handle, "\n");
        fwrite($handle, $def->getMarkdown());
        $this->nl($handle);

        foreach ($def->getImages() as $image) {
            fwrite($handle, sprintf('![%s](img/%s)', basename($image, '.png'), basename($image)));
            $this->nl($handle);
        }

        $this->hr($handle);
        fwrite($handle, "* [Go to Overview](Home)\n");
        foreach ($refs as $ref) {
            fwrite($handle, sprintf("* See also %s\n", $ref->getMarkdownLink()));
        }
        if ($prev) {
            fwrite($handle, sprintf("* Previous: %s\n", $prev->getMarkdownLink()));
        }
        if ($next) {
            fwrite($handle, sprintf("* Next: %s\n", $next->getMarkdownLink()));
        }
    }

    /**
     * Write the home site.
     *
     * @param string $title
     * @param Definition[] $definitions
     */
    private function writeHomePage(string $title, array $definitions)
    {
        $handle = fopen($this->buildFilename('Home'), 'w');
        fwrite($handle, '# '.$title);
        $this->nl($handle);

        $letter = null;
        foreach ($definitions as $definition) {
            if ($definition->isEmpty()) {
                continue;
            }

            $thisLetter = preg_replace('/[^a-z]/', '#', $definition->getEscapedName()[0]);
            if ($letter !== $thisLetter) {
                $letter = $thisLetter;
                $this->hr($handle);
            }
            fwrite($handle, '* ' . $definition->getMarkdownLink() . "\n");
        }
        $this->hr($handle);
        fclose($handle);
    }

    /**
     * Writes a tag overview page.
     *
     * @param string[] $tags
     */
    private function writeTagsPage(array $tags)
    {
        $handle = fopen($this->buildFilename('Tags'), 'w');
        fwrite($handle, '# Tags');
        $this->nl($handle);
        foreach ($tags as $tag) {
            fwrite($handle, "* [#$tag]($tag)\n");
        }

        fclose($handle);
    }

    /**
     * Writes a sidebar.
     *
     * @param string[] $tags
     */
    private function writeSidebar(array $tags)
    {
        $handle = fopen($this->buildFilename('_Sidebar'), 'w');
        fwrite($handle, '[**Overview**](Home)');
        $this->nl($handle);
        fwrite($handle, '[**Tags**](Tags)');
        $this->nl($handle);
        foreach ($tags as $tag) {
            fwrite($handle, "* [#$tag]($tag)\n");
        }

        fclose($handle);
    }

    /**
     * Writes a sidebar.
     * @param string $author
     */
    private function writeFooter(string $author)
    {
        $handle = fopen($this->buildFilename('_Footer'), 'w');
        fwrite($handle, '*Last updated at ' . date('Y-m-d H:i:s') . '*');
        $this->nl($handle);
        fwrite($handle, '*Author:* ' . $author);

        fclose($handle);
    }

    /**
     * @param array $entries
     * @param array $refs
     * @param Definition $def
     * @param Definition $last
     * @param Definition $next
     * @return array
     */
    private function writeEntry(array $entries, array $refs, $def, $last = null, $next = null)
    {
        $name = $def->getEscapedName();
        if (isset($entries[$name])) {
            unset($entries[$name]);
        }

        $handle = fopen($this->directory . '/' . $name . '.md', 'w');
        $this->writeOutWikiEntry($handle, $refs, $def, $last, $next);
        fclose($handle);

        $this->copyImages($def);

        return $entries;
    }

    /**
     * Copies images of a definition to the wiki.
     *
     * @param Definition $definition
     */
    private function copyImages(Definition $definition)
    {
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
    private function emptyImages(): void
    {
        $dir = $this->directory . '/img/';
        if (!is_dir($dir)) return;

        $imageDir = opendir($dir);
        while (false !== ($entry = readdir($imageDir))) {
            if ('.' === $entry[0]) {
                continue;
            }

            unlink($dir . $entry);
        }
        closedir($imageDir);
    }

    /**
     * @param string[] $entries
     */
    private function deleteEntries(array $entries)
    {
        foreach (array_keys($entries) as $entry) {
            unlink($this->directory . '/' . $entry . '.md');
        }
    }

    /**
     * Writes sites for each tag.
     * @param Definition[][] $taggedDefinitions
     */
    private function writeTags(array $taggedDefinitions)
    {
        foreach ($taggedDefinitions as $tag => $definitions) {
            $this->writeTag($tag, $definitions);
        }
    }

    /**
     * @param string $tagName
     * @param Definition[] $definitions
     */
    private function writeTag(string $tagName, array $definitions)
    {
        if (!is_dir($this->directory . '/tags'))
            mkdir($this->directory . '/tags', 0777, true);

        $handle = fopen($this->directory . '/tags/' . $tagName . '.md', 'w');
        if (!is_resource($handle)) throw new \RuntimeException('Cannot create tag');

        fwrite($handle, '# Tag #' . $tagName);
        $this->nl($handle);
        foreach ($definitions as $definition) {
            fwrite($handle, '* ' . $definition->getMarkdownLink() . "\n");
        }
        $this->hr($handle);
        fwrite($handle, "* [Go to Overview](Home)\n");
        fclose($handle);
    }

    /**
     * @param $handle
     */
    private function hr($handle)
    {
        fwrite($handle, "\n\n***\n\n");
    }

    /**
     * @param $handle
     */
    private function nl($handle)
    {
        fwrite($handle, "\n\n");
    }

    /**
     * @param string $relative
     * @param string $extension
     * @return string
     */
    private function buildFilename($relative, $extension = 'md')
    {
        return $this->directory . '/' . $relative . '.' . $extension;
    }
}
