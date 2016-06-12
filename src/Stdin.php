<?php

namespace Clue\React\Stdio;

use React\Stream\ReadableStream;
use React\Stream\Stream;
use React\EventLoop\LoopInterface;

// TODO: only implement ReadableStream
class Stdin extends Stream
{
    private $oldMode = null;

    public function __construct(LoopInterface $loop)
    {
        parent::__construct(STDIN, $loop);

        // support starting program with closed STDIN ("example.php 0<&-")
        // the stream is a valid resource and is not EOF, but fstat fails
        if (fstat(STDIN) === false) {
            return $this->close();
        }

        if ($this->isTty()) {
            $this->oldMode = shell_exec('stty -g');

            // Disable icanon (so we can fread each keypress) and echo (we'll do echoing here instead)
            shell_exec('stty -icanon -echo');
        }
    }

    public function close()
    {
        $this->restore();
        parent::close();
    }

    public function __destruct()
    {
        $this->restore();
    }

    private function restore()
    {
        if ($this->oldMode !== null && $this->isTty()) {
            // Reset stty so it behaves normally again
            shell_exec(sprintf('stty %s', $this->oldMode));
            $this->oldMode = null;
        }
    }

    private function isTty()
    {
        if (is_resource(STDIN)) {
            $stat = fstat(STDIN);
            if (isset($stat['mode']) && ($stat['mode'] & 0170000) === 0020000) {
                // this is a character device (console / TTY)
                return true;
            }
        }
        return false;
    }
}
