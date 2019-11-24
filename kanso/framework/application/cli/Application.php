<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\application\cli;

use kanso\framework\application\Application as BaseApplication;
use kanso\framework\application\cli\commands\GenerateSecret;
use kanso\framework\application\cli\commands\Encrypt;
use kanso\framework\cli\Cli;
use kanso\framework\cli\Environment;
use kanso\framework\cli\input\Input;
use kanso\framework\cli\output\Formatter;
use kanso\framework\cli\output\Output;
use kanso\framework\console\Console;

/**
 * Kanso framework main class file.
 *
 * @author Joe J. Howard
 */
class Application extends BaseApplication
{
    /**
     * Sets up the framework core.
     */
    protected function initialize(): void
    {
        parent::initialize();

        $this->container->singleton('Cli', function($container)
        {
            return new Cli($container->Input, $container->output, new Environment);
        });

        $this->container->singleton('Input', function()
        {
            return new Input($_SERVER['argv']);
        });

        $this->container->singleton('Output', function()
        {
            return new Output(new Formatter, new Environment);
        });

        $this->container->singleton('Console', function($container)
        {
            return new Console($container->Input, $container->Output, $container);
        });
    }

    /**
     * Run the application.
     */
    public function run(): void
    {
        // Register reactor commands
        foreach($this->getCommands() as $command => $class)
        {
            $this->container->Console->registerCommand($command, $class);
        }

        // Run the reactor
        exit($this->container->Console->run());
    }

    /**
     * Returns all registered commands.
     *
     * @return array
     */
    protected function getCommands(): array
    {
        // Define core commands
        $commands =
        [
            'generate_secret' => GenerateSecret::class,
            'encrypt'         => Encrypt::class,
        ];

        // Add application commands
        $commands += $this->container->Config->get('application.commands');

        // Return commands
        return $commands;
    }
}
