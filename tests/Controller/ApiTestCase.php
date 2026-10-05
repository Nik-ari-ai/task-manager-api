<?php

namespace App\Tests\Controller;

use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);

        $this->resetSchema();
        $this->loadDefaultStatuses();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }

    private function resetSchema(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    private function loadDefaultStatuses(): void
    {
        $definitions = [
            ['name' => 'new', 'title' => 'New'],
            ['name' => 'in_progress', 'title' => 'In progress'],
            ['name' => 'done', 'title' => 'Done'],
        ];

        foreach ($definitions as $definition) {
            $status = new Status();
            $status->setName($definition['name']);
            $status->setTitle($definition['title']);
            $status->setSystem(true);

            $this->entityManager->persist($status);
        }

        $this->entityManager->flush();
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeResponse(): array
    {
        $content = $this->client->getResponse()->getContent();

        if (false === $content || '' === $content) {
            return [];
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
