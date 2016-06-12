<?php

class FunctionalExampleTest extends TestCase
{
    public function testPeriodicExampleWithClosedInputQuitsImmediately()
    {
        $output = $this->execExample('php periodic.php 0<&-');

        $this->assertContains('STDIN closed', $output);
    }

    public function testPeriodicExampleWithPipedInputEndsBecauseInputEnds()
    {
        $output = $this->execExample('echo yet | php periodic.php');

        $this->assertContains('STDIN closed', $output);
    }

    private function execExample($command)
    {
        chdir(__DIR__ . '/../examples/');

        return shell_exec($command);
    }
}
