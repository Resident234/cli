<?php


namespace Cmd;


class InputBase
{
    private $inputs = null;
    protected static $error_text_allowed = '';
    protected static $error_text_required = '';
    protected static $help_text_title = '';
    protected static $help_text_uninstalled = '';

    /**
     * InputBase constructor.
     * @param $allowed_inputs
     */
    public function __construct($allowed_inputs)
    {
        $this->inputs = $allowed_inputs;
    }

    /**
     * @param $command_inputs
     * @throws \Exception
     */
    public function validate($command_inputs)
    {
        if ($this->inputs) {
            if ($allowed_inputs = array_keys($this->inputs)) {
                if ($unallowed_inputs = array_diff($command_inputs, $allowed_inputs)) {
                    throw new \Exception(sprintf(static::$error_text_allowed, implode(', ', $unallowed_inputs)));
                }
            }
            $required_inputs = array();
            foreach ($this->inputs as $input_name => $input) {
                if ($input['required']) {
                    $required_inputs[] = $input_name;
                }
            }
            if ($unset_inputs = array_diff($required_inputs, $command_inputs)) {
                throw new \Exception(sprintf(static::$error_text_required, implode(', ', $unset_inputs)));
            }
        }
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        $help = static::$help_text_title;
        if ($this->inputs) {
            foreach ($this->inputs as $argumentName => $argumentData) {
                $help .= PHP_EOL;
                $help .= "{$argumentName} - {$argumentData['description']}, " . ($argumentData['required'] ? 'Обязательный' : 'Не обязательный');
            }
        } else {
            $help .= static::$help_text_uninstalled;
        }
        return $help;
    }
}