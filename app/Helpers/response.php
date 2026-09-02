<?php
function json_response(mixed $data, int $status = 200): never { \Skoolyst\Core\Response::json($data, $status); }
