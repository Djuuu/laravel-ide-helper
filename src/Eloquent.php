<?php

/**
 * Laravel IDE Helper to add \Eloquent mixin to Eloquent\Model
 *
 * @author Charles A. Peterson <artistan@gmail.com>
 */

namespace Barryvdh\LaravelIdeHelper;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock\Context;
use Barryvdh\Reflection\DocBlock\Serializer as DocBlockSerializer;
use Barryvdh\Reflection\DocBlock\Tag;

class Eloquent
{
    /** @var array[] */
    private const FILES_TO_ANNOTATE = [
        [
            'class'       => 'Illuminate\Database\Eloquent\Model',
            'originalDoc' => 'abstract class Model implements',
            'mixins'      => [
                '\Eloquent',
                '\Illuminate\Database\Eloquent\Builder',
            ],
        ],
        [
            'class'       => 'Illuminate\Database\Eloquent\Builder',
            'originalDoc' => 'class Builder',
            'mixins'      => [
                '\Illuminate\Database\Query\Builder',
            ],
        ],
        [
            'class'       => 'Illuminate\Database\Eloquent\Relations\Relation',
            'originalDoc' => 'abstract class Relation',
            'mixins'      => [
                '\Illuminate\Database\Eloquent\Builder',
            ],
        ],
    ];

    /** @var Command */
    private $command;

    /** @var Filesystem */
    private $files;

    /**
     * Write mixin helper to the Eloquent\Model
     * This is needed since laravel/framework v5.4.29
     *
     * @param Command    $command
     * @param Filesystem $files
     *
     * @return void
     */
    public static function writeEloquentModelHelper(Command $command, Filesystem $files)
    {
        $eloquent = new self($command, $files);

        foreach (static::FILES_TO_ANNOTATE as $item) {
            $eloquent->writeMixins($item['class'], $item['originalDoc'], $item['mixins']);
        }
    }

    /**
     * @param Command    $command
     * @param Filesystem $files
     */
    private function __construct(Command $command, Filesystem $files)
    {
        $this->command = $command;
        $this->files   = $files;
    }

    /**
     * @param string $class
     * @param string $originalDocDefault
     * @param array  $expectedMixins
     *
     * @return void
     */
    private function writeMixins($class, $originalDocDefault, $expectedMixins)
    {
        $reflection  = new \ReflectionClass($class);
        $namespace   = $reflection->getNamespaceName();
        $originalDoc = $reflection->getDocComment();

        $phpdoc = new DocBlock($reflection, new Context($namespace));

        $mixins = $phpdoc->getTagsByName('mixin');

        $expectedMixins = array_fill_keys($expectedMixins, false);

        foreach ($mixins as $m) {
            $mixin = $m->getContent();

            if (isset($expectedMixins[$mixin])) {
                $this->command->info('Tag Exists: @mixin '.$mixin.' in '.$class);

                $expectedMixins[$mixin] = true;
            }
        }

        $changed = false;
        foreach ($expectedMixins as $expectedMixin => $present) {
            if ($present === false) {
                $phpdoc->appendTag(Tag::createInstance('@mixin '.$expectedMixin, $phpdoc));

                $changed = true;
            }
        }

        // If nothing's changed, stop here.
        if (!$changed) {
            return;
        }

        $serializer = new DocBlockSerializer();
        $serializer->getDocComment($phpdoc);
        $docComment = $serializer->getDocComment($phpdoc);

        /*
            The new DocBlock is appended to the beginning of the class declaration.
            Since there is no DocBlock, the declaration is used as a guide.
        */
        if (!$originalDoc) {
            $originalDoc = $originalDocDefault;
            $docComment  .= "\n".$originalDocDefault;
        }

        $filename = $reflection->getFileName();

        if (!$filename) {
            $this->command->error('Filename not found '.$class);
            return;
        }

        $contents = $this->files->get($filename);
        if (!$contents) {
            $this->command->error('No file contents found '.$filename);
            return;
        }

        $count    = 0;
        $contents = str_replace($originalDoc, $docComment, $contents, $count);
        if ($count === 0) {
            $this->command->error('Content did not change '.$contents);
            return;
        }

        if ($this->files->put($filename, $contents)) {
            $this->command->info('Wrote expected docblock to '.$filename);
        } else {
            $this->command->error('File write failed to '.$filename);
        }
    }
}
