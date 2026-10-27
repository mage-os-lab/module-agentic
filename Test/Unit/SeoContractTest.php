<?php

declare(strict_types=1);

namespace MageOS\Agentic\Test\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Production code uses MageOS_Seo only through its `Api` interfaces.
 */
class SeoContractTest extends TestCase
{
    /**
     * @return void
     */
    public function testOnlySeosApiIsReferenced(): void
    {
        $root    = \dirname(__DIR__, 2);
        $scanned = [];
        $outside = [];

        foreach ($this->productionFiles($root) as $relative) {
            $scanned[] = $relative;
            $contents  = (string) file_get_contents($root . '/' . $relative);

            // One backslash in XML and `use` statements, two inside a PHP string literal.
            preg_match_all('/MageOS\\\\{1,2}Seo\\\\{1,2}([\w\\\\]+)/', $contents, $matches);
            foreach ($matches[1] as $name) {
                $name = (string) preg_replace('/\\\\+/', '\\', $name);
                if (!str_starts_with($name, 'Api\\')) {
                    $outside[] = $relative . ': MageOS\\Seo\\' . $name;
                }
            }
        }

        $this->assertContains('Controller/Wellknown/Index.php', $scanned);
        $this->assertContains('etc/di.xml', $scanned);

        $outside = array_values(array_unique($outside));
        sort($outside);
        $this->assertSame([], $outside, 'Seo is used outside its Api: use the Api interface instead.');
    }

    /**
     * The module's production files, relative to its root: PHP outside Test/, and XML under etc/.
     *
     * @param string $root
     * @return string[]
     */
    private function productionFiles(string $root): array
    {
        $directories = new \RecursiveCallbackFilterIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
            static function (\SplFileInfo $file, string $path, \RecursiveDirectoryIterator $iterator): bool {
                $name = $file->getFilename();
                if (str_starts_with($name, '.')) {
                    return false;
                }

                return $iterator->getSubPath() !== '' || !\in_array($name, ['Test', 'vendor'], true);
            }
        );

        $files = [];
        /** @var \SplFileInfo $file */
        foreach (new \RecursiveIteratorIterator($directories) as $file) {
            $relative  = substr($file->getPathname(), \strlen($root) + 1);
            $extension = $file->getExtension();
            if ($extension === 'php' || ($extension === 'xml' && str_starts_with($relative, 'etc/'))) {
                $files[] = $relative;
            }
        }
        sort($files);

        return $files;
    }
}
