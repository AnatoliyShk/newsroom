<?php

namespace App\Controller;

use App\Dto\RegisterRequest;
use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


final class RegisterController extends AbstractController
{
    #[Route('/api/auth/register', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] RegisterRequest $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $user = (new User())
            ->setEmail(mb_strtolower($request->email))
            ->setRoles(['ROLE_AUTHOR']);
        $user->setPassword($passwordHasher->hashPassword($user, $request->password));
        $entityManager->persist($user);
        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['error' => 'email_taken'], Response::HTTP_CONFLICT);
        }
        return $this->json(
            [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
            ],
            Response::HTTP_CREATED
        );
    }
}
