<?php

namespace CornyPhoenix\Component\Glossary\Generator;

use CornyPhoenix\Component\Glossary\Definition\Definition;
use CornyPhoenix\Component\Glossary\Glossary;

/**
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary\Generator
 */
class WebsiteGenerator extends AbstractGenerator
{

    /**
     * @var string
     */
    protected $templateLocation;

    /**
     * Wiki constructor.
     * @param string $directory
     * @param string $templateLocation
     */
    public function __construct(string $directory, string $templateLocation) {
        parent::__construct('html', $directory);
        $this->templateLocation = $templateLocation;
    }

    /**
     * Generates the wiki.
     *
     * @param Glossary $glossary
     */
    public function generate(Glossary $glossary) {
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
        $this->writeIndexPage($glossary->getMeta('title'), $glossary->getDefinitions(), $glossary->getTags());

        // Write aggregated pages.
        $this->writeTagsPage($glossary->getTags());
        $this->writeSidebar($glossary->getTags());
        $this->writeTags($glossary->getTaggedDefinitions());
    }

    /**
     * @return array
     */
    private function readCurrentEntries(): array {
        $entries = [];
        $dir = opendir($this->directory);
        if ($dir === false) {
            return [];
        }

        while (false !== ($entry = readdir($dir))) {
            if ('.' === $entry[0]) {
                continue;
            }
            if ('.html' !== substr($entry, -5)) {
                continue;
            }

            $entries[substr($entry, 0, -5)] = true;
        }

        closedir($dir);

        return $entries;
    }

    /**
     * @param Glossary $glossary
     * @param string[] $entries
     * @return string[]
     */
    private function writeEntries(Glossary $glossary, array $entries): array {
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
            if (isset($entries[$current->getName()])) {
                unset($entries[$current->getName()]);
            }
            $this->writeEntry($glossary, $refs, $current, $last, $next);
        }
        $refs = isset($map[$next->getName()]) ? $map[$next->getName()] : [];
        if (isset($entries[$next->getName()])) {
            unset($entries[$next->getName()]);
        }
        $this->writeEntry($glossary, $refs, $next, $current);

        return $entries;
    }

    protected function buildEntry(
        Glossary $glossary,
        array $refs,
        Definition $def,
        Definition $prev = null,
        Definition $next = null
    ): string {
        $body = $def->getHtml();

        if (count($def->getTags())) {
            $body .= "<h2>Tagged with</h2><ul><li>";
            $implode = implode(
                '</li><li>',
                array_map(
                    function ($tag) {
                        return "<a class='tag' href='tags/$tag.html'>#$tag</a>";
                    },
                    $def->getTags()
                )
            );
            $body .= "$implode</li></ul>";
        }

        foreach ($def->getImages() as $image) {
            $body .= sprintf('![%s](img/%s)', basename($image, '.png'), basename($image));
            $this->nl($body);
        }

        if (!empty($refs)) {
            $body .= '<h2>See also</h2><ul>';
            foreach ($refs as $ref) {
                $body .= sprintf("<li>%s</li>", $ref->getHtmlLink());
            }
            $body .= '</ul>';
        }

        $this->hr($body);
        if ($prev) {
            $body .= sprintf("<a class='btn prev' href='%s.html'>%s</a>", $prev->getEscapedName(), $prev->getName());
        }
        if ($next) {
            $body .= sprintf("<a class='btn next' href='%s.html'>%s</a>", $next->getEscapedName(), $next->getName());
        }

        return $this->render('entry', $def->getName(), $body, $glossary->getTags());
    }

    /**
     * Write the home site.
     *
     * @param string $title
     * @param Definition[] $definitions
     * @param string[] $tags
     */
    private function writeIndexPage(string $title, array $definitions, array $tags) {
        $letter = null;
        $open = false;
        $body = '';
        foreach ($definitions as $definition) {
            $thisLetter = preg_replace('/[^a-z]/', '#', $definition->getEscapedName()[0]);
            if ($letter !== $thisLetter) {
                $letter = $thisLetter;
                if ($open) $body .= '</ul>';
                $open = true;
                $body .= '<h2>'.strtoupper($letter).'</h2><ul>';
            }
            $body .= '<li>'.$definition->getHtmlLink().'</li>';
        }
        if ($open) $body .= '</ul>';

        $handle = fopen($this->buildFilename('index'), 'w');
        fwrite($handle, $this->render('index', $title, $body, $tags));
        fclose($handle);
    }

    /**
     * Writes a tag overview page.
     *
     * @param string[] $tags
     */
    private function writeTagsPage(array $tags) {
        $title = 'Tags';
        $body = '<ul>';
        foreach ($tags as $tag) {
            $body .= "<li><a href='tags/$tag.html'>#$tag</a></li>";
        }
        $body .= '</ul>';

        $handle = fopen($this->buildFilename('Tags'), 'w');
        fwrite($handle, $this->render('tags', $title, $body, $tags));
        fclose($handle);
    }

    /**
     * Writes a sidebar.
     *
     * @param string[] $tags
     */
    private function writeSidebar(array $tags) {
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
     * Copies all images to the wiki.
     */
    private function emptyImages(): void {
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

    /**
     * @param string[] $entries
     */
    private function deleteEntries(array $entries) {
        foreach (array_keys($entries) as $entry) {
            unlink($this->directory.'/'.$entry.'.md');
        }
    }

    /**
     * Writes sites for each tag.
     * @param Definition[][] $taggedDefinitions
     */
    private function writeTags(array $taggedDefinitions) {
        foreach ($taggedDefinitions as $tag => $definitions) {
            $this->writeTag($tag, $definitions, array_keys($taggedDefinitions));
        }
    }

    /**
     * @param string $tagName
     * @param Definition[] $definitions
     * @param string[] $tags
     */
    private function writeTag(string $tagName, array $definitions, array $tags) {
        $title = 'Tag #'.$tagName;
        $body = '<ul>';
        foreach ($definitions as $definition) {
            $body .= '<li>'.$definition->getHtmlLink().'</li>';
        }
        $body .= '</ul>';

        // Write out tag
        if (!is_dir($this->directory.'/tags')) {
            mkdir($this->directory.'/tags', 0777, true);
        }
        $handle = fopen($this->buildFilename('tags/'.$tagName), 'w');
        fwrite($handle, $this->render('tag', $title, $body, $tags));
        fclose($handle);
    }

    /**
     * @param string $string
     */
    private function hr(string &$string) {
        $string .= "<hr/>";
    }

    /**
     * @param $handle
     */
    private function nl($handle) {
        fwrite($handle, "\n\n");
    }

    /**
     * Renders a template with content.
     *
     * @param string $title
     * @param string $body
     * @param string[] $tags
     * @return string
     */
    private function render(string $class, string $title, string $body, array $tags): string {
        ob_start();
        include $this->templateLocation;
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }
}
