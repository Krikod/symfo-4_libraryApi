<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $manager;
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create("fr_FR");
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->loadAdherent();
        $this->loadPret();
        $manager->flush();
    }

    /**
     * Création des adhérents
     */
    public function loadAdherent()
    {

    }

    /**
     * Création des prêts
     */
    public function loadPret()
    {

    }
}
