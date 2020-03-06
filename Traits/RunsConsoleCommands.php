<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 1/14/20
 * Time: 10:05 PM
 */

namespace CircleLinkHealth\Core\Traits;

use Symfony\Component\Process\Process;


trait RunsConsoleCommands
{
    public function runCommand(array $command, bool $echoOutput = true)
    {
        $process = new Process($command);
        
        echo PHP_EOL.'Running command:';
        
        foreach ($command as $c) {
            echo " $c ";
        }
        
        echo PHP_EOL;
        
        $process->run();
        
        $output = (string) trim($process->getOutput());
        
        if (true === $echoOutput) {
            echo $output;
        }
        
        if (0 !== $process->getExitCode()) {
            echo $process->getErrorOutput();
            throw new \Exception($process->getExitCodeText().' when executing '.$process->getCommandLine());
        }
        
        return $process;
    }
}