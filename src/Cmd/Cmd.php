<?php

namespace Cmd;

use Cmd\Util\Terminal;

/**
 * @method Cmd command (string $name = null)
 * @method Cmd arguments (array $option = null)
 * @method Cmd parameters (array $option = null)
 * @method Cmd description (string $description)
 * @method Cmd action (\Closure $action)
 * @method Cmd run ()
 */

class Cmd
{
    const OPTION_TYPE_ARGUMENT  = 1; // например {arg1,arg2,arg3} или {arg1}
    const OPTION_TYPE_PARAMETER = 2; // например [name=value] или [name={value1,value2,value3}]

    private
        $current_command             = null,
        $file_name                  = null,
        $tokens                     = array(),
        $nameless_option_counter    = 0,
        $parsed                     = false;
    public
        $command_name               = null,
        $arguments                  = array(),
        $parameters                 = array();


    /**
     * @var Command[]
     */
    private $commands = array();

    /**
     * @var array
     */
    public static $methods = array(
        'command'       => 'command',
        'argument'      => 'argument',
        'parameter'     => 'parameter',
        'description'   => 'description',
        'run'           => 'run',
        'action'        => 'action',
        'arguments'     => 'arguments',
        'parameters'    => 'parameters'
    );

    /**
     * @param array|null $tokens
     */
    public function __construct($tokens = null)
    {
        if (empty($tokens)) {
            $tokens = $_SERVER['argv'];
        }
        $this->setTokens($tokens);
    }

    /**
     * @throws \Exception
     */
    public function __destruct()
    {
        if (!$this->parsed) {
            $this->parse();
        }
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return Cmd
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (empty(self::$methods[$name])) {
            throw new \Exception(sprintf('Unknown function, %s, called', $name));
        }

        if (empty($this->current_command) && $name !== 'command') {
            throw new \Exception('Invalid Command');
        }

        array_unshift($arguments, $this->current_command);
        $option = call_user_func_array(array($this, "_$name"), $arguments);

        return $this;
    }

    /**
     * @param Command|null $option
     * @param string|int $name
     * @return Command
     * @throws \Exception
     */
    private function _command($option, $name = null)
    {
        if (isset($name) && !empty($this->commands[$name])) {
            $this->current_command = $this->getCommand($name);
        } else {
            if (!isset($name)) {
                $name = 'autonamed' . ++$this->nameless_option_counter;
            }
            $this->current_command = $this->commands[$name] = new Command($name);
        }
        return $this->current_command;
    }

    /**
     * @param Command $option
     * @param string $description
     * @return Command
     */
    private function _description(Command $option, $description)
    {
        return $option->setDescription($description);
    }

    /**
     * @param \Cmd\Command $option
     * @param $arguments
     * @return \Cmd\Command
     */
    private function _arguments(Command $option, $arguments)
    {
        return $option->setArguments($arguments);
    }

    /**
     * @param \Cmd\Command $option
     * @param $parameters
     * @return \Cmd\Command
     */
    private function _parameters(Command $option, $parameters)
    {
        return $option->setParameters($parameters);
    }

    /**
     * @param Command $option
     * @param \Closure $callback (string $value) -> boolean
     * @return Command
     */
    private function _action(Command $option, \Closure $callback)
    {
        return $option->setAction($callback);
    }

    /**
     * @param $option Command
     * @return bool
     * @throws \Exception
     */
    private function _run(Command $option)
    {
        $this->parseIfNotParsed();
        if ($this->command_name) {
            $this->validate();
            if ($current_command = $this->commands[$this->command_name]) {
                $current_command->execute($this);
            } else {
                throw new \Exception('Command is not defined');
            }
        }
        return true;
    }

    /**
     * @param array $cli_tokens
     * @return Cmd
     */
    public function setTokens(array $cli_tokens)
    {
        $this->tokens = $cli_tokens;
        return $this;
    }

    /**
     * @throws \Exception
     * @return void
     */
    private function parseIfNotParsed()
    {
        if ($this->isParsed()) {
            return;
        }
        $this->parse();
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function parse()
    {
        $this->parsed = true;
        try {
            $tokens = $this->tokens;

            // Имя исполняемого файла
            $this->file_name = array_shift($tokens);

            if ($tokens) {
                // Название команды
                $this->command_name = array_shift($tokens);

                while ($tokens) {
                    $token = array_shift($tokens);
                    list($values, $type) = $this->_parseOption($token);
                    if ($type === self::OPTION_TYPE_ARGUMENT) {
                        // Аргументы запуска
                        foreach ($values as $value) {
                            $this->arguments[] = $value;
                        }
                    } elseif ($type === self::OPTION_TYPE_PARAMETER) {
                        // Параметры запуска
                        foreach ($values as $key => $value) {
                            $this->parameters[$key] = $value;
                        }
                    }
                }

                if (in_array('help', $this->arguments)) {
                    $this->printHelp();
                    exit;
                }
            } else {
                $this->printCommands();
                exit;
            }
        } catch(\Exception $e) {
            $this->error($e);
        }
    }

    /**
     * @throws \Exception
     */
    private function validate()
    {
        try {
            $option = $this->getCommand($this->command_name);
            if ($option->arguments) {
                $option->arguments->validate($this->arguments);
            }
            if ($option->parameters) {
                $option->parameters->validate($this->parameters);
            } 
        } catch (\Exception $e) {
            $this->error($e);
        }
    }

    /**
     * @param \Exception $e
     *
     * @throws \Exception
     */
    public function error(\Exception $e)
    {
        Terminal::beep();
        $error = sprintf('ERROR: %s ', $e->getMessage());
        echo $error . PHP_EOL;
        exit(1);
    }

    /**
     * @return bool
     */
    public function isParsed()
    {
        return $this->parsed;
    }

    /**
     * @param string $token
     *
     * @return array
     * @throws \Exception
     */
    private function _parseOption($token)
    {
        $matches = array();
        $result = array();
        if (preg_match('#\{(.*?)\}#', $token, $matches)) {
            $arguments = explode(',', $matches[1]);
            $result = array($arguments, self::OPTION_TYPE_ARGUMENT);
        }
        if (preg_match('#\[(.*?)\]#', $token, $matches)) {
            $parameter = explode('=', $matches[1]);
            if (preg_match('#\{(.*?)\}#', $parameter[1], $matches)) {
                $result = array(array($parameter[0] => explode(',', $matches[1])), self::OPTION_TYPE_PARAMETER);
            } else {
                $result = array(array($parameter[0] => $parameter[1]), self::OPTION_TYPE_PARAMETER);
            }
        }
        if (!$result) {
            throw new \Exception(sprintf('Unable to parse option %s: Invalid syntax', $token));
        }

        return $result;
    }


    /**
     * @param $command_name
     * @return Command
     * @throws \Exception if $option does not exist
     */
    public function getCommand($command_name)
    {
        if (!$this->hasCommand($command_name)) {
            throw new \Exception(sprintf('Unknown command, %s, specified', $command_name));
        }

        return $this->commands[$command_name];
    }

    /**
     * @param $command_name
     * @return boolean
     */
    public function hasCommand($command_name)
    {
        return !empty($this->commands[$command_name]);
    }

    /**
     * @throws \Exception
     */
    private function printHelp()
    {
        $help = Terminal::header($this->file_name) . PHP_EOL;
        $help .= PHP_EOL;

        $option = $this->getCommand($this->command_name);
        $help .= $option->getHelpDescription() . PHP_EOL;
        if ($help_arguments = $option->getHelpArguments()) {
            $help .= PHP_EOL;
            $help .= $help_arguments . PHP_EOL;
        }
        if ($help_parameters = $option->getHelpParameters()) {
            $help .= PHP_EOL;
            $help .= $help_parameters . PHP_EOL;
        }
        echo $help;
    }

    /**
     * @throws \Exception
     */
    private function printCommands()
    {
        $help = Terminal::header($this->file_name) . PHP_EOL;
        $help .= PHP_EOL;

        $keys = array_keys($this->commands);
        natsort($keys);
        foreach ($keys as $key) {
            $option = $this->getCommand($key);
            $help .= $option->getHelpDescription() . PHP_EOL;
        }
        echo $help;
    }
}
