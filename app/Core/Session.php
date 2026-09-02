<?php
declare(strict_types=1);

namespace Skoolyst\Core;

class Session {
    public static function start(): void {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    }
}
