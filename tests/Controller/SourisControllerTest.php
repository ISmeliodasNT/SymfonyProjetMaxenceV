<?php

namespace App\Tests\Controller;

use App\Entity\Souris;
use App\Repository\SourisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SourisControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $souriRepository;
    private string $path = '/souris/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->souriRepository = $this->manager->getRepository(Souris::class);

        foreach ($this->souriRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Souri index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'souri[marque]' => 'Testing',
            'souri[prix]' => 'Testing',
            'souri[description]' => 'Testing',
            'souri[stock]' => 'Testing',
            'souri[status]' => 'Testing',
            'souri[connectivite]' => 'Testing',
            'souri[NbBoutons]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->souriRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Souris();
        $fixture->setMarque('My Title');
        $fixture->setPrix('My Title');
        $fixture->setDescription('My Title');
        $fixture->setStock('My Title');
        $fixture->setStatus('My Title');
        $fixture->setConnectivite('My Title');
        $fixture->setNbBoutons('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Souri');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Souris();
        $fixture->setMarque('Value');
        $fixture->setPrix('Value');
        $fixture->setDescription('Value');
        $fixture->setStock('Value');
        $fixture->setStatus('Value');
        $fixture->setConnectivite('Value');
        $fixture->setNbBoutons('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'souri[marque]' => 'Something New',
            'souri[prix]' => 'Something New',
            'souri[description]' => 'Something New',
            'souri[stock]' => 'Something New',
            'souri[status]' => 'Something New',
            'souri[connectivite]' => 'Something New',
            'souri[NbBoutons]' => 'Something New',
        ]);

        self::assertResponseRedirects('/souris/');

        $fixture = $this->souriRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getMarque());
        self::assertSame('Something New', $fixture[0]->getPrix());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getStock());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame('Something New', $fixture[0]->getConnectivite());
        self::assertSame('Something New', $fixture[0]->getNbBoutons());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Souris();
        $fixture->setMarque('Value');
        $fixture->setPrix('Value');
        $fixture->setDescription('Value');
        $fixture->setStock('Value');
        $fixture->setStatus('Value');
        $fixture->setConnectivite('Value');
        $fixture->setNbBoutons('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/souris/');
        self::assertSame(0, $this->souriRepository->count([]));
    }
}
