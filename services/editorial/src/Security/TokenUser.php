<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUser;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

final class TokenUser extends JWTUser
{
    public function __construct(
        string $userIdentifier,
        array $roles,
        private readonly int $id,
    ) {
        parent::__construct($userIdentifier, $roles);
    }

    public static function createFromPayload($username, array $payload): JWTUserInterface
    {
        if(!isset($payload['id'])) {
            throw new UserNotFoundException('Token has no user id.');
        }

        return new self($username, $payload['roles'] ?? [], (int)$payload['id']);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
