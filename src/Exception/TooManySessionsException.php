<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(429, [
    'Retry-After' => 10,
])]
class TooManySessionsException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Trop de sessions actives. Déconnectez-vous d\'un appareil avant de vous reconnecter.');
    }
}