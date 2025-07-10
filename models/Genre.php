<?php
namespace App\Models;

use PDO;

class Genre extends BaseModel
{
    protected string $table = 'genres';

    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);
    }
}