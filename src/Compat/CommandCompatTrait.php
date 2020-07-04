<?php

namespace Barryvdh\LaravelIdeHelper\Compat;

/**
 * Trait CommandCompatTrait
 *
 * @mixin \Illuminate\Console\Command
 */
trait CommandCompatTrait
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->handle();
    }
}
