<?php

namespace CornyPhoenix\Component\Glossary\Generator;

use CornyPhoenix\Component\Glossary\Definition\ReferenceDefinition;
use CornyPhoenix\Component\Glossary\Glossary;

/**
 * Class LaTeXGenerator.
 *
 * @author Konstantin Simon Maria Möllers
 * @package CornyPhoenix\Component\Glossary
 */
class LaTeXGenerator implements GeneratorInterface
{

    /**
     * @var string[]
     */
    public static $latexEquivalents = array(
        0x0009 => ' ',
        0x0023 => '{\#}',
        0x0025 => '{\%}',
        0x0026 => '{\&}',
        0x003c => '{\textless}',
        0x003e => '{\textgreater}',
        0x005b => '{\[}',
        0x005c => '{\textbackslash}',
        0x005d => '{\]}',
        0x005e => '{\textasciicircum}',
        0x005f => '{\_}',
        0x007b => '{\{}',
        0x007d => '{\}}',
        0x007e => '{\textasciitilde}',
        0x00a0 => '{~}',
        0x00a1 => '{!`}',
        0x00a2 => '{\\not{c}}',
        0x00a3 => '{\\pounds}',
        0x00a7 => '{\\S}',
        0x00a8 => '{\\"{}}',
        0x00a9 => '{\\copyright}',
        0x00af => '{\\={}}',
        0x00ac => '{\\neg}',
        0x00ad => '{\\-}',
        0x00b0 => '{\\mbox{$^\\circ$}}',
        0x00b1 => '{\\mbox{$\\pm$}}',
        0x00b2 => '{\\mbox{$^2$}}',
        0x00b3 => '{\\mbox{$^3$}}',
        0x00b4 => "{\\'{}}",
        0x00b5 => '{\\mbox{$\\mu$}}',
        0x00b6 => '{\\P}',
        0x00b7 => '{\\mbox{$\\cdot$}}',
        0x00b8 => '{\\c{}}',
        0x00b9 => '{\\mbox{$^1$}}',
        0x00bf => '{?`}',
        0x00c0 => '{\\`A}',
        0x00c1 => "{\\'A}",
        0x00c2 => '{\\^A}',
        0x00c3 => '{\\~A}',
        0x00c4 => '{\\"A}',
        0x00c5 => '{\\AA}',
        0x00c6 => '{\\AE}',
        0x00c7 => '{\\c{C}}',
        0x00c8 => '{\\`E}',
        0x00c9 => "{\\'E}",
        0x00ca => '{\\^E}',
        0x00cb => '{\\"E}',
        0x00cc => '{\\`I}',
        0x00cd => "{\\'I}",
        0x00ce => '{\\^I}',
        0x00cf => '{\\"I}',
        0x00d1 => '{\\~N}',
        0x00d2 => '{\\`O}',
        0x00d3 => "{\\'O}",
        0x00d4 => '{\\^O}',
        0x00d5 => '{\\~O}',
        0x00d6 => '{\\"O}',
        0x00d7 => '{\\mbox{$\\times$}}',
        0x00d8 => '{\\O}',
        0x00d9 => '{\\`U}',
        0x00da => "{\\'U}",
        0x00db => '{\\^U}',
        0x00dc => '{\\"U}',
        0x00dd => "{\\'Y}",
        0x00df => '{\\ss}',
        0x00e0 => '{\\`a}',
        0x00e1 => "{\\'a}",
        0x00e2 => '{\\^a}',
        0x00e3 => '{\\~a}',
        0x00e4 => '{\\"a}',
        0x00e5 => '{\\aa}',
        0x00e6 => '{\\ae}',
        0x00e7 => '{\\c{c}}',
        0x00e8 => '{\\`e}',
        0x00e9 => "{\\'e}",
        0x00ea => '{\\^e}',
        0x00eb => '{\\"e}',
        0x00ec => '{\\`\\i}',
        0x00ed => "{\\'\\i}",
        0x00ee => '{\\^\\i}',
        0x00ef => '{\\"\\i}',
        0x00f1 => '{\\~n}',
        0x00f2 => '{\\`o}',
        0x00f3 => "{\\'o}",
        0x00f4 => '{\\^o}',
        0x00f5 => '{\\~o}',
        0x00f6 => '{\\"o}',
        0x00f7 => '{\\mbox{$\\div$}}',
        0x00f8 => '{\\o}',
        0x00f9 => '{\\`u}',
        0x00fa => "{\\'u}",
        0x00fb => '{\\^u}',
        0x00fc => '{\\"u}',
        0x00fd => "{\\'y}",
        0x00ff => '{\\"y}',

        0x0100 => '{\\=A}',
        0x0101 => '{\\=a}',
        0x0102 => '{\\u{A}}',
        0x0103 => '{\\u{a}}',
        0x0104 => '{\\c{A}}',
        0x0105 => '{\\c{a}}',
        0x0106 => "{\\'C}",
        0x0107 => "{\\'c}",
        0x0108 => "{\\^C}",
        0x0109 => "{\\^c}",
        0x010a => "{\\.C}",
        0x010b => "{\\.c}",
        0x010c => "{\\v{C}}",
        0x010d => "{\\v{c}}",
        0x010e => "{\\v{D}}",
        0x010f => "{\\v{d}}",
        0x0112 => '{\\=E}',
        0x0113 => '{\\=e}',
        0x0114 => '{\\u{E}}',
        0x0115 => '{\\u{e}}',
        0x0116 => '{\\.E}',
        0x0117 => '{\\.e}',
        0x0118 => '{\\c{E}}',
        0x0119 => '{\\c{e}}',
        0x011a => "{\\v{E}}",
        0x011b => "{\\v{e}}",
        0x011c => '{\\^G}',
        0x011d => '{\\^g}',
        0x011e => '{\\u{G}}',
        0x011f => '{\\u{g}}',
        0x0120 => '{\\.G}',
        0x0121 => '{\\.g}',
        0x0122 => '{\\c{G}}',
        0x0123 => '{\\c{g}}',
        0x0124 => '{\\^H}',
        0x0125 => '{\\^h}',
        0x0128 => '{\\~I}',
        0x0129 => '{\\~\\i}',
        0x012a => '{\\=I}',
        0x012b => '{\\=\\i}',
        0x012c => '{\\u{I}}',
        0x012d => '{\\u\\i}',
        0x012e => '{\\c{I}}',
        0x012f => '{\\c{i}}',
        0x0130 => '{\\.I}',
        0x0131 => '{\\i}',
        0x0132 => '{IJ}',
        0x0133 => '{ij}',
        0x0134 => '{\\^J}',
        0x0135 => '{\\^\\j}',
        0x0136 => '{\\c{K}}',
        0x0137 => '{\\c{k}}',
        0x0139 => "{\\'L}",
        0x013a => "{\\'l}",
        0x013b => "{\\c{L}}",
        0x013c => "{\\c{l}}",
        0x013d => "{\\v{L}}",
        0x013e => "{\\v{l}}",
        0x0141 => '{\\L}',
        0x0142 => '{\\l}',
        0x0143 => "{\\'N}",
        0x0144 => "{\\'n}",
        0x0145 => "{\\c{N}}",
        0x0146 => "{\\c{n}}",
        0x0147 => "{\\v{N}}",
        0x0148 => "{\\v{n}}",
        0x014c => '{\\=O}',
        0x014d => '{\\=o}',
        0x014e => '{\\u{O}}',
        0x014f => '{\\u{o}}',
        0x0150 => '{\\H{O}}',
        0x0151 => '{\\H{o}}',
        0x0152 => '{\\OE}',
        0x0153 => '{\\oe}',
        0x0154 => "{\\'R}",
        0x0155 => "{\\'r}",
        0x0156 => "{\\c{R}}",
        0x0157 => "{\\c{r}}",
        0x0158 => "{\\v{R}}",
        0x0159 => "{\\v{r}}",
        0x015a => "{\\'S}",
        0x015b => "{\\'s}",
        0x015c => "{\\^S}",
        0x015d => "{\\^s}",
        0x015e => "{\\c{S}}",
        0x015f => "{\\c{s}}",
        0x0160 => "{\\v{S}}",
        0x0161 => "{\\v{s}}",
        0x0162 => "{\\c{T}}",
        0x0163 => "{\\c{t}}",
        0x0164 => "{\\v{T}}",
        0x0165 => "{\\v{t}}",
        0x0168 => "{\\~U}",
        0x0169 => "{\\~u}",
        0x016a => "{\\=U}",
        0x016b => "{\\=u}",
        0x016c => "{\\u{U}}",
        0x016d => "{\\u{u}}",
        0x016e => "{\\r{U}}",
        0x016f => "{\\r{u}}",
        0x0170 => "{\\H{U}}",
        0x0171 => "{\\H{u}}",
        0x0172 => "{\\c{U}}",
        0x0173 => "{\\c{u}}",
        0x0174 => "{\\^W}",
        0x0175 => "{\\^w}",
        0x0176 => "{\\^Y}",
        0x0177 => "{\\^y}",
        0x0178 => '{\\"Y}',
        0x0179 => "{\\'Z}",
        0x017a => "{\\'Z}",
        0x017b => "{\\.Z}",
        0x017c => "{\\.Z}",
        0x017d => "{\\v{Z}}",
        0x017e => "{\\v{z}}",

        0x01c4 => "{D\\v{Z}}",
        0x01c5 => "{D\\v{z}}",
        0x01c6 => "{d\\v{z}}",
        0x01c7 => "{LJ}",
        0x01c8 => "{Lj}",
        0x01c9 => "{lj}",
        0x01ca => "{NJ}",
        0x01cb => "{Nj}",
        0x01cc => "{nj}",
        0x01cd => "{\\v{A}}",
        0x01ce => "{\\v{a}}",
        0x01cf => "{\\v{I}}",
        0x01d0 => "{\\v\\i}",
        0x01d1 => "{\\v{O}}",
        0x01d2 => "{\\v{o}}",
        0x01d3 => "{\\v{U}}",
        0x01d4 => "{\\v{u}}",
        0x01e6 => "{\\v{G}}",
        0x01e7 => "{\\v{g}}",
        0x01e8 => "{\\v{K}}",
        0x01e9 => "{\\v{k}}",
        0x01ea => "{\\c{O}}",
        0x01eb => "{\\c{o}}",
        0x01f0 => "{\\v\\j}",
        0x01f1 => "{DZ}",
        0x01f2 => "{Dz}",
        0x01f3 => "{dz}",
        0x01f4 => "{\\'G}",
        0x01f5 => "{\\'g}",
        0x01fc => "{\\'\\AE}",
        0x01fd => "{\\'\\ae}",
        0x01fe => "{\\'\\O}",
        0x01ff => "{\\'\\o}",

        0x02c6 => '{\\^{}}',
        0x02dc => '{\\~{}}',
        0x02d8 => '{\\u{}}',
        0x02d9 => '{\\.{}}',
        0x02da => "{\\r{}}",
        0x02dd => '{\\H{}}',
        0x02db => '{\\c{}}',
        0x02c7 => '{\\v{}}',

        0x03c0 => '{\\mbox{$\\pi$}}',

        0xfb01 => '{fi}',
        0xfb02 => '{fl}',

        0x2013 => '{--}',
        0x2014 => '{---}',
        0x2018 => "{`}",
        0x2019 => "{'}",
        0x201c => "{``}",
        0x201d => "{''}",
        0x2020 => "{\\dag}",
        0x2021 => "{\\ddag}",
        0x20ac => '{\texteuro}',
        0x2122 => '{\\mbox{$^\\mbox{TM}$}}',
        0x2022 => '{\\mbox{$\\bullet$}}',
        0x2026 => '{\\ldots}',
        0x2202 => '{\\mbox{$\\partial$}}',
        0x220f => '{\\mbox{$\\prod$}}',
        0x2211 => '{\\mbox{$\\sum$}}',
        0x221a => '{\\mbox{$\\surd$}}',
        0x221e => '{\\mbox{$\\infty$}}',
        0x222b => '{\\mbox{$\\int$}}',
        0x2248 => '{\\mbox{$\\approx$}}',
        0x2260 => '{\\mbox{$\\neq$}}',
        0x2264 => '{\\mbox{$\\leq$}}',
        0x2265 => '{\\mbox{$\\geq$}}',
    );

    /**
     * @var string|null
     */
    private $filename;

    /**
     * LaTeXGenerator constructor.
     *
     * @param string|null $filename
     */
    public function __construct(string $filename = null) {
        $this->filename = $filename;
    }

    /**
     * Escaped einen String für LaTeX.
     *
     * @param string $string
     * @return string
     */
    public static function escape($string) {
        $new = '';

        foreach (preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY) as $utf8Char) {
            list(, $ord) = unpack('N', \mb_convert_encoding($utf8Char, 'UCS-4BE', 'UTF-8'));
            if (isset(self::$latexEquivalents[$ord])) {
                $new .= self::$latexEquivalents[$ord];
            } else {
                $new .= $utf8Char;
            }
        }

        return str_replace("\n", "\n\n", $new);
    }

    /**
     * Generates the glossary output from a model.
     *
     * @param Glossary $glossary The model to generate the output from.
     */
    public function generate(Glossary $glossary) {
        if ($this->filename === null) {
            $this->buildLaTeXString($glossary);
            return;
        }

        $this->writeOutLaTeXFile($glossary);
    }

    /**
     * Generates a LaTeX string from the definitions.
     *
     * @param Glossary $glossary
     * @return string
     */
    public function buildLaTeXString(Glossary $glossary): string {
        $line = $this->buildFrontMatter($glossary);

        foreach ($glossary->getDefinitions() as $name => $definition) {
            $escaped = $definition->getEscapedName();
            $options = [
                'name' => LaTeXGenerator::escape($name),
                'description' => $definition->getLaTeX(),
            ];

            if ($definition instanceof ReferenceDefinition) {
                $options['see'] = $glossary->getDefinition($definition->getReferences())->getEscapedName();
            }

            $parsedOptions = [];
            foreach ($options as $key => $value) {
                $parsedOptions[] = $key.'={'.$value.'}';
            }
            $line .= sprintf("\\newglossaryentry{%s}{%s}\n", $escaped, implode(',', $parsedOptions));
        }

        return $line;
    }

    /**
     * @param Glossary $glossary
     */
    public function writeOutLaTeXFile(Glossary $glossary) {
        $handle = fopen($this->filename, 'w');
        fwrite($handle, $this->buildLaTeXString($glossary));
        fclose($handle);
    }

    /**
     * @param Glossary $glossary
     * @return string
     */
    private function buildFrontMatter(Glossary $glossary): string {
        $line = '';
        foreach ($glossary->getAllMeta() as $key => $value) {
            $line .= sprintf('\%s{%s}', $key, $value);
            $line .= "\n";
        }

        return $line;
    }
}
