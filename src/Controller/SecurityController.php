<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class SecurityController extends AbstractController
{
    #[Route('/authentication_token', methods: ['POST'])]
    public function login(
        Request $request,
        JWTTokenManagerInterface $JWTManager,
        UserProviderInterface $userProvider,
        UserPasswordEncoderInterface $passwordEncoder,
    ): JsonResponse
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return new JsonResponse(['last_username' => $lastUsername, 'error' => $error ? $error->getMessage() : null]);
    }

    #[Route('/logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
