<?php

namespace App\Tests\Controller;

use App\Entity\Clavier;
use App\Repository\ClavierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ClavierControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $clavierRepository;
    private string $path = '/clavier/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->clavierRepository = $this->manager->getRepository(Clavier::class);

        foreach ($this->clavierRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Clavier index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'clavier[marque]' => 'Testing',
            'clavier[prix]' => 'Testing',
            'clavier[description]' => 'Testing',
            'clavier[stock]' => 'Testing',
            'clavier[status]' => 'Testing',
            'clavier[switch]' => 'Testing',
            'clavier[language]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->clavierRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Clavier();
        $fixture->setMarque('My Title');
        $fixture->setPrix('My Title');
        $fixture->setDescription('My Title');
        $fixture->setStock('My Title');
        $fixture->setStatus('My Title');
        $fixture->setSwitch('My Title');
        $fixture->setLanguage('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Clavier');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Clavier();
        $fixture->setMarque('Value');
        $fixture->setPrix('Value');
        $fixture->setDescription('Value');
        $fixture->setStock('Value');
        $fixture->setStatus('Value');
        $fixture->setSwitch('Value');
        $fixture->setLanguage('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'clavier[marque]' => 'Something New',
            'clavier[prix]' => 'Something New',
            'clavier[description]' => 'Something New',
            'clavier[stock]' => 'Something New',
            'clavier[status]' => 'Something New',
            'clavier[switch]' => 'Something New',
            'clavier[language]' => 'Something New',
        ]);

        self::assertResponseRedirects('/clavier/');

        $fixture = $this->clavierRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getMarque());
        self::assertSame('Something New', $fixture[0]->getPrix());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getStock());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame('Something New', $fixture[0]->getSwitch());
        self::assertSame('Something New', $fixture[0]->getLanguage());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Clavier();
        $fixture->setMarque('Value');
        $fixture->setPrix('Value');
        $fixture->setDescription('Value');
        $fixture->setStock('Value');
        $fixture->setStatus('Value');
        $fixture->setSwitch('Value');
        $fixture->setLanguage('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/clavier/');
        self::assertSame(0, $this->clavierRepository->count([]));
    }
}
