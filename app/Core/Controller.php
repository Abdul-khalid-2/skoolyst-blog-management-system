<?php
declare(strict_types=1);

namespace Skoolyst\Core;

abstract class Controller {
    protected function view(string $view, array $data = []): mixed { return View::render($view, $data); }
    protected function json(mixed $data, int $status = 200): never {
        Response::json($data, $status);
    }
}
