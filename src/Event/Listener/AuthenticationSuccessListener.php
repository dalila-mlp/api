<?php

namespace App\Event\Listener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuthenticationSuccessListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        if (!($user = $event->getUser()) instanceof UserInterface) return;

        $event->setData(array_merge(
            $event->getData(),
            ['id' => $user->getId()]
        ));
    }
}
