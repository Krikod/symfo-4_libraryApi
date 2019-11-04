<?php

namespace App\DataFixtures;

use App\Entity\Adherent;
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
        $genre = ['male', 'female'];
        $commune = [
            "78003", "78005", "78006", "78007", "78009", "78010", "78013", "78015", "78020", "78029",
            "78030", "78031", "78033", "78034", "78036", "78043", "78048", "78049", "78050", "78053", "78057",
            "78062", "78068", "78070", "78071", "78072", "78073", "78076", "78077", "78082", "78084", "78087",
            "78089", "78090", "78092", "78096", "78104", "78107", "78108", "78113", "78117", "78118"
        ];

        for ($i=0;$i<25;$i++) {
            $adherent = new Adherent();
            $adherent->setNom($this->faker->lastName)
                ->setPrenom($this->faker->firstName($genre[mt_rand(0,1)]))
                ->setAdresse($this->faker->streetAddress)
                ->setTel($this->faker->phoneNumber)
                ->setCodeCommune($commune[mt_rand(0,sizeof($commune)-1)])
                ->setMail(strtolower($adherent->getNom()) . "@gmail.com")
                ->setPassword($adherent->getNom());

            $this->addReference('adherent-'.$i, $adherent);// Meth. de AbstractFixture.php: on s'en servira pour affecter un prêt à un adhérent
            $this->manager->persist($adherent);
        }
        $adherent = new Adherent();
        $adherent->setNom("Kod")
            ->setPrenom("Kri")
            ->setMail("admin@gmail.com")
            ->setPassword("Kod");
        $this->manager->persist($adherent);
        
        $this->manager->flush();
    }

    /**
     * Création des prêts
     */
    public function loadPret()
    {

    }
}
