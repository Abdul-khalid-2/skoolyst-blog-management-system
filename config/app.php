<?php
return ['name' => $_ENV['APP_NAME'] ?? 'Skoolyst Module', 'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)];
