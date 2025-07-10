<?php
namespace App\Models;

use PDO;

class Person extends BaseModel
{
    protected string $table = 'people';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }
}