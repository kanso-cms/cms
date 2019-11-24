<?php

/**
 * @copyright Joe J. Howard
 * @license   https:#github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\framework\cli\Input;

use kanso\framework\utility\Str;

/**
 * CLI input parser.
 *
 * @author Joe J. Howard
 */
class Input
{
	/**
	 * Base command.
	 *
	 * @var string
	 */
	private $command;

	/**
	 * Sub command if it exists.
	 *
	 * @var string|null
	 */
	private $subCmd;

	/**
	 * Option flags.
	 *
	 * @var array
	 */
	private $options = [];

	/**
	 * Option flags with values.
	 *
	 * @var array
	 */
	private $params = [];

    /**
     * Constructor.
     *
     * @param array $arguments Array of CLI input arguments
     */
    public function __construct(array $arguments = [])
    {
        $this->setCommand($arguments);

        $this->setSubCommand($arguments);

        $this->parseArgs($this->sanitizeArgArray($arguments));
    }

    /**
     * Returns the parameters.
     *
     * @return string
     */
    public function command(): string
    {
        return $this->command;
    }

    /**
     * Returns the sub-command (if it exists).
     *
     * @return string|null
     */
    public function subCommand()
    {
        return $this->subCmd;
    }

    /**
     * Returns the parameters.
     *
     * @return array
     */
    public function parameters(): array
    {
        return $this->params;
    }

    /**
     * Returns the option flags.
     *
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * Sets the current command.
     *
     * @param array $arguments Array of CLI input arguments
     */
    private function setCommand(array $arguments): void
    {
        $this->command = $arguments[0];
    }

    /**
     * Sets the current sub-command (if one was provided).
     *
     * @param array $arguments Array of CLI input arguments
     */
    private function setSubCommand(array $arguments): void
    {
        // Remove initial command
        array_shift($arguments);

        if (isset($arguments[0]) && $arguments[0][0] !== '-')
        {
            $this->subCmd = $arguments[0];
        }
    }

    /**
     * Sanitizes the initial CLI args.
     *
     * @param  array $args Array of CLI input arguments
     * @return array
     */
    private function sanitizeArgArray(array $args): array
    {
        // Remove the initial command
        array_shift($args);

        // If a subcommand was supplied remove it too
        if (!is_null($this->subCmd))
        {
            array_shift($args);
        }

        return array_values(array_filter(array_map('trim', $args)));
    }

	/**
	 * Parses the initial CLI args.
	 *
	 * @param array $args Sanitized Array of CLI input arguments
	 */
	private function parseArgs(array $args): void
	{
		foreach ($args as $i => $arg)
		{
			// "--" long form args
			if ($this->isLongFormArg($arg))
			{
                $arg = substr($arg, 2);

				// contains "=" e.g '--foo=bar'
				if (Str::contains($arg, '='))
				{
					$option = explode('=', $arg);

					$this->params[$this->sanitizeArgKey($option[0])] = $this->sanitizeArgValue($option[1]);
				}

				// Does not contain "=" e.g '--foo bar'
				elseif ($this->nextArgIsValue($args, $i))
				{
                    $this->params[$this->sanitizeArgKey($arg)] = $this->sanitizeArgValue($args[$i+1]);

					unset($args[$i+1]);
				}

                // Arg is just flag e.g. '--foo'
                else
                {
                    $this->options[] = $this->sanitizeArgKey($arg);
                }
			}

			// "-" short form
			elseif ($arg[0] === '-')
			{
                $arg = substr($arg, 1);

                // multiple flags in one e.g '-fbs'
                if (isset($arg[1]) && $arg[1] !== '=' && isset($arg[2]))
                {
                    $this->options = array_values(array_unique(array_merge($this->options, str_split($arg))));
                }

				// contains "=" e.g '-f=bar'
				elseif (isset($arg[1]) && $arg[1] === '=')
				{
					$option = explode('=', $arg);

					$this->params[trim($option[0])] = trim($option[1]);
				}

                // Does not contain "=" e.g '-f bar'
                elseif ($this->nextArgIsValue($args, $i))
                {
                    $this->params[$this->sanitizeArgKey($arg)] = $this->sanitizeArgValue($args[$i+1]);

                    unset($args[$i+1]);
                }

                // Single letter argument e.g "-f"
                else
                {
                    $this->options[] = $this->sanitizeArgKey($arg);
                }
			}
		}
	}

    /**
     * Sanitize an arg key/flag.
     *
     * @param  string $arg Raw arg
     * @return string
     */
    private function sanitizeArgKey(string $arg): string
    {
        return preg_replace('/^(--(.*)|-(.*))$/', '$2$3', $arg);
    }

    /**
     * Sanitize an arg value.
     *
     * @param  string $arg Raw arg
     * @return string
     */
    private function sanitizeArgValue(string $arg): string
    {
        return preg_replace('/^(\'(.*)\'|"(.*)")$/', '$2$3', $arg);
    }

    /**
     * Checks if the arg was provided as a long form flag.
     *
     * @param  string $arg Raw arg
     * @return bool
     */
    private function isLongFormArg(string $arg): bool
    {
        return isset($arg[0]) && isset($arg[1]) && $arg[0] === '-' && $arg[1] === '-';
    }

    /**
     * Checks if the next arg in the args array is a value.
     *
     * @param  array $args    Args array
     * @param  int   $current Current array pointer
     * @return bool
     */
    private function nextArgIsValue(array $args, int $current): bool
    {
        $next = $current + 1;

        return isset($args[$next]) && $args[$next][0] !== '-';
    }
}
