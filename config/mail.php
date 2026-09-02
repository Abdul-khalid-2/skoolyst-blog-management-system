<?php
return ['driver' => 'smtp', 'host' => $_ENV['MAIL_HOST'] ?? '', 'port' => $_ENV['MAIL_PORT'] ?? 587];
