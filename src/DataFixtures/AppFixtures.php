<?php

namespace App\DataFixtures;

use App\Entity\Adherent;
use App\Entity\Livre;
use App\Entity\Pret;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $manager;
    private $faker;
    private $repoLivre;
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->faker = Factory::create("fr_FR");
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->repoLivre = $this->manager->getRepository(Livre::class);
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
                ->setPassword($this->passwordEncoder
                    ->encodePassword($adherent, $adherent->getNom()));

            $this->addReference('adherent'.$i, $adherent);// Meth. de AbstractFixture.php: on s'en servira pour affecter un prêt à un adhérent
            $this->manager->persist($adherent);
        }
        $adherentAdmin = new Adherent();
        $roleAdmin[] = Adherent::ROLE_ADMIN;
        $adherentAdmin->setNom("Kod")
            ->setPrenom("Kri")
            ->setMail("admin@gmail.com")
            ->setPassword($this->passwordEncoder
                ->encodePassword($adherentAdmin, $adherentAdmin->getNom()))
            ->setRoles($roleAdmin);
        $this->manager->persist($adherentAdmin);

        $adherentManager = new Adherent();
        $roleManager[] = Adherent::ROLE_MANAGER;
        $adherentManager->setNom("Durand")
            ->setPrenom("Sophie")
            ->setMail("manager@gmail.com")
            ->setPassword($this->passwordEncoder
                ->encodePassword($adherentManager, $adherentManager->getNom()))
            ->setRoles($roleManager);
        $this->manager->persist($adherentManager);

        $this->manager->flush();
    }

    /**
     * Création des prêts
     */
    public function loadPret()
    {
        for ($i=0;$i<25;$i++ ){ // pour chaque adhérent
            $max = mt_rand(1,5);
            for ($j=0;$j<=$max;$j++){ // création des prêts
                $pret = new Pret();
                $livre = $this->repoLivre->find(mt_rand(1,49));
                $pret->setLivre($livre)
                    ->setAdherent($this->getReference('adherent'.$i))
                    ->setDatePret($this->faker->dateTimeBetween('-6 months'));
                $dateRetourPrevue = date('Y-m-d H:m:n', strtotime('15 days', $pret->getDatePret()->getTimestamp()));
                $dateRetourPrevue = \DateTime::createFromFormat('Y-m-d H:m:n',$dateRetourPrevue);
                $pret->setDateRetourPrevue($dateRetourPrevue);

                if (mt_rand(1,3) == 1) {
                    $pret->setDateRetourReelle($this->faker->dateTimeInInterval($pret->getDatePret(), "+30 days"));
                }
                $this->manager->persist($pret);
            }
        }
        $this->manager->flush();
    }
}
