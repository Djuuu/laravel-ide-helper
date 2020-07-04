<?php

namespace Barryvdh\LaravelIdeHelper\Console;

use Barryvdh\LaravelIdeHelper\Compat\CommandCompatTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AllCommand
 */
class AllCommand extends Command
{
    use CommandCompatTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'ide-helper:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shortcut to :models, :generate and :meta';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->line('artisan ide-helper:models');

        Artisan::call('ide-helper:models', $this->getModelOptions(), $this->getOutput());


        $this->line('artisan ide-helper:generate');

        Artisan::call('ide-helper:generate', [], $this->getOutput());


        $this->line('artisan ide-helper:meta');

        Artisan::call('ide-helper:meta', [], $this->getOutput());
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['write',   'W', InputOption::VALUE_NONE, 'ide-helper:models - Write to Model file'],
            ['nowrite', 'N', InputOption::VALUE_NONE, 'ide-helper:models - Don\'t write to Model file (default)'],
            ['reset',   'R', InputOption::VALUE_NONE, 'ide-helper:models - Remove the original phpdocs instead of appending'],
        ];
    }

    /**
     * @return array
     */
    protected function getModelOptions(): array
    {
        $modelOptions = [];

        if ($this->option('write')) {
            $modelOptions['--write'] = true;
        } else {
            $modelOptions['--nowrite'] = true;
        }

        if ($this->option('reset')) {
            $modelOptions['--reset'] = true;
        }

        return $modelOptions;
    }
}
