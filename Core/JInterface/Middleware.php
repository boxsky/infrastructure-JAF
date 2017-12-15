<?php
namespace JAF\Core\JInterface;

interface Middleware {
    const STEP_CONTINUE = 1;
    const STEP_BREAK = 2;
    const STEP_EXIT = 3;

    function handle();
}