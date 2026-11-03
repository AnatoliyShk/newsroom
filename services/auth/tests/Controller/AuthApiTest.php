<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class AuthApiTest extends WebTestCase
{
    private const EMAIL = 'test@newsroom.test';
    private const PASSWORD = 'correct-horse-battery';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        self::getContainer()->get(EntityManagerInterface::class)
            ->createQuery('DELETE FROM App\Entity\User')
            ->execute();
    }

    public function testRegisterCreatesAnAuthor(): void
    {
        $this->register();

        self::assertResponseStatusCodeSame(201);
        self::assertContains('ROLE_AUTHOR', $this->responseJson()['roles']);
    }

    public function testRegisterIgnoresRolesSentByTheClient(): void
    {
        $this->register(['roles' => ['ROLE_EDITOR']]);

        self::assertResponseStatusCodeSame(201);
        self::assertNotContains('ROLE_EDITOR', $this->responseJson()['roles']);
    }

    public function testRegisterRefusesADuplicateEmail(): void
    {
        $this->register();
        $this->register();

        self::assertResponseStatusCodeSame(409);
    }

    public function testRegisterRefusesAShortPassword(): void
    {
        $this->register(['password' => 'short']);

        self::assertResponseStatusCodeSame(422);
    }

    public function testRegisterRefusesAMissingPassword(): void
    {
        $this->client->jsonRequest('POST', '/api/auth/register', ['email' => self::EMAIL]);

        self::assertResponseStatusCodeSame(422);
    }

    public function testLoginRefusesAWrongPassword(): void
    {
        $this->register();
        $this->client->jsonRequest('POST', '/api/auth/login', ['email' => self::EMAIL, 'password' => 'wrong-password-here']);

        self::assertResponseStatusCodeSame(401);
    }

    public function testMeNeedsAToken(): void
    {
        $this->client->jsonRequest('GET', '/api/users/me');

        self::assertResponseStatusCodeSame(401);
    }

    public function testMeReturnsTheLoggedInUser(): void
    {
        $this->register();
        $this->me($this->login());

        self::assertResponseIsSuccessful();
        self::assertSame(self::EMAIL, $this->responseJson()['email']);
    }

    public function testPromoteGivesTheEditorRole(): void
    {
        $this->register();

        $commandTester = new CommandTester((new Application(self::$kernel))->find('app:user:promote'));
        self::assertSame(0, $commandTester->execute(['email' => self::EMAIL, 'role' => 'ROLE_EDITOR']));

        $this->me($this->login());
        self::assertContains('ROLE_EDITOR', $this->responseJson()['roles']);
    }

    private function register(array $overrides = []): void
    {
        $this->client->jsonRequest('POST', '/api/auth/register', [
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
            ...$overrides,
        ]);
    }

    private function login(): string
    {
        $this->client->jsonRequest('POST', '/api/auth/login', ['email' => self::EMAIL, 'password' => self::PASSWORD]);

        return $this->responseJson()['token'];
    }

    private function me(string $token): void
    {
        $this->client->jsonRequest('GET', '/api/users/me', [], ['HTTP_AUTHORIZATION' => 'Bearer '.$token]);
    }

    private function responseJson(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
