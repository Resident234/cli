<?php


namespace Cmd;


class Parameters extends InputBase
{
    protected static $error_text_allowed = 'Установлены неразрешенные параметры запуска %s';
    protected static $error_text_required = 'Не установлены обязательные параметры запуска %s';
    protected static $help_text_title = 'Параметры запуска: ';
    protected static $help_text_uninstalled = 'Допустимые входящие параметры не заданы';

    /**
     * @param $command_inputs
     * @throws \Exception
     */
    public function validate($command_inputs)
    {
        $command_inputs = array_keys($command_inputs);
        parent::validate($command_inputs);
    }
}