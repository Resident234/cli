<?php

namespace Cmd;


class Command
{
    private
        $name, /* string */
        $description, /* string */
        $action; /* closure */
    public
        $arguments = null,
        $parameters = null;

    /**
     * @param string $name
     * @return void
     * @throws \Exception
     */
    public function __construct($name)
    {
        if (!$name) {
            throw new \Exception(sprintf('Invalid option name %s', $name));
        }
        $this->name = $name;
    }

    /**
     * @param string $description
     * @return Command
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param $arguments
     * @return $this
     */
    public function setArguments($arguments)
    {
        $this->arguments = new Arguments($arguments);
        return $this;
    }

    /**
     * @param $parameters
     * @return $this
     */
    public function setParameters($parameters)
    {
        $this->parameters = new Parameters($parameters);
        return $this;
    }

    /**
     * @param \Closure $action
     * @return Command
     */
    public function setAction(\Closure $action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param $current_command
     * @return bool
     */
    public function execute($current_command)
    {
        if (!is_callable($this->action))
            return true;

        return call_user_func($this->action, $current_command);
    }

    /**
     * @return string
     */
    public function getHelpDescription()
    {
        return "{$this->name}: " . ($this->description ?: 'Описание команды не задано');
    }

    /**
     * @return false
     */
    public function getHelpArguments()
    {
        /** @var Arguments $this->arguments */
        return $this->arguments ? $this->arguments->getHelp() : false;
    }

    /**
     * @return false
     */
    public function getHelpParameters()
    {
        /** @var Parameters $this->parameters */
        return $this->parameters ? $this->parameters->getHelp() : false;
    }
}
