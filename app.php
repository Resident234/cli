<?php

use Cmd\Util\Terminal;

require_once 'vendor/autoload.php';

$cmd = new Cmd\Cmd();

$description = 'Команда , которая принимает на вход неограниченное количество аргументов и выводит их в читаемом виде';
$cmd->command('print')
    ->description($description)
    ->action(function($cmd) {
        echo "Called command: {$cmd->command_name}" . PHP_EOL . PHP_EOL;
        echo "Arguments:" . PHP_EOL;
        if ($cmd->arguments) {
            foreach ($cmd->arguments as $argument) {
                echo " - " . $argument . PHP_EOL;
            }
        }
        echo PHP_EOL;
        echo "Options:" . PHP_EOL;
        if ($cmd->parameters) {
            foreach ($cmd->parameters as $parameterName => $parameterValue) {
                echo " - " . $parameterName . PHP_EOL;
                if (is_array($parameterValue)) {
                    foreach ($parameterValue as $parameterValueItem) {
                        echo "   " . " - " . $parameterValueItem . PHP_EOL;
                    }
                } else {
                    echo "   " . " - " . $parameterValue . PHP_EOL;
                }

            }
        }
        return true;
    });

$cmd->command('test1')
    ->description('Тестовое описание команды test1')
    ->arguments([
        'arg1' => [
            'description' => 'Описание аргумента 1',
            'required' => true
        ],
        'arg2' => [
            'description' => 'Описание аргумента 2',
            'required' => true
        ],
        'arg3' => [
            'description' => 'Описание аргумента 3',
            'required' => false
        ],
        'arg4' => [
            'description' => 'Описание аргумента 4',
            'required' => false
        ]
    ])
    ->parameters([
        'param1' => [
            'description' => 'Описание параметра 1',
            'required' => true
        ],
        'param2' => [
            'description' => 'Описание параметра 2',
            'required' => true
        ],
        'param3' => [
            'description' => 'Описание параметра 3',
            'required' => false
        ],
        'param4' => [
            'description' => 'Описание параметра 4',
            'required' => false
        ]
    ]);
$cmd->command('test2')->description('Тестовое описание команды test2');
$cmd->command('test3')->description('Тестовое описание команды test3');
$cmd->command('test4')->description('Тестовое описание команды test4');
$cmd->command('test5')->description('Тестовое описание команды test5');
$cmd->command('test6')->description('Тестовое описание команды test6');
$cmd->command('test7');

$cmd->run();