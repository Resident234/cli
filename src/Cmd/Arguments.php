<?php


namespace Cmd;


class Arguments extends InputBase
{
    protected static $error_text_allowed = 'Установлены неразрешенные аргументы запуска %s';
    protected static $error_text_required = 'Не установлены обязательные аргументы запуска %s';
    protected static $help_text_title = 'Аргументы запуска: ';
    protected static $help_text_uninstalled = 'Допустимые входящие аргументы не заданы';

    /**
     * @param $command_inputs
     * @throws \Exception
     */
    public function validate($command_inputs)
    {
        $command_inputs = array_values($command_inputs);
        parent::validate($command_inputs);
    }
}