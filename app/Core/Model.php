<?php
declare(strict_types=1);

namespace Skoolyst\Core;

abstract class Model {
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
}
