<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Repository\AuteurRepository;
use App\Repository\NationaliteRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiAuteurController extends AbstractController
{
    /**
     * @Route("/api/auteurs", name="api_auteurs", methods={"GET"})
     */
    public function list(AuteurRepository $repo, SerializerInterface $serializer)
    {
        $auteurs = $repo->findAll();
        $resultat = $serializer->serialize(
          $auteurs,
          'json',
          [
              'groups' => ['ListeAuteurFull']// Soit liste d'éléments à
              // sérialiser, soit @Groups ds Entity (éviter circular reference)
          ]
        );
        return new JsonResponse($resultat, 200, [], true);
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_show", methods={"GET"})
     */
    public function show(Auteur $auteur, SerializerInterface $serializer)
    {
        $resultat = $serializer->serialize(
            $auteur,
            'json',
            [
                'groups' => ['ListeAuteurSimple']
            ]
        );
//        return new JsonResponse($resultat, 200, [], true);
        return new JsonResponse($resultat, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/auteurs",
     *     name="api_auteurs_create",
     *     methods={"POST"})
     */
    public function create(Request $request,
                           ObjectManager $manager,
                           NationaliteRepository $nationaliteRepository,
                           SerializerInterface $serializer,
                           ValidatorInterface $validator)
    {
        $data = $request->getContent();
        $dataTab = $serializer->decode($data, 'json');
        $auteur = new Auteur();
        $nationalite = $nationaliteRepository->find($dataTab['nationalite']['id']);
        $serializer->deserialize($data,Auteur::class,'json',
            ['object_to_populate' => $auteur]);
        $auteur->setNationalite($nationalite);


        // Gestion des erreurs de validation
        $errors = $validator->validate($auteur);

        if (count($errors)) {
            $errorsJson = $serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($auteur);
        $manager->flush();

// Pour une création pas de résultat donné ici (null, ou message).
// Dans [] = ce qu'on rajoute dans le header.
//        return new JsonResponse(
//            "Le auteur a bien été créé.",
//            Response::HTTP_CREATED,
//            ["location" => "api/auteurs/" . $auteur->getId()],
//            true);

//        Ou, pour renvoyer l'url absolue:
            return new JsonResponse(
                "L'auteur a bien été créé.",
                Response::HTTP_CREATED,
            ["location" => $this->generateUrl(
                'api_auteurs_show',
                ["id" => $auteur->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )], true);
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_update", methods={"PUT"})
     */
    public function edit(Auteur $auteur, Request $request,
                         NationaliteRepository $nationaliteRepository,
                         ObjectManager $manager,
                         SerializerInterface $serializer,
                         ValidatorInterface $validator)
    {
        $data = $request->getContent();

        // Variable pour contenir le format tableau
        $dataTab = $serializer->decode($data, 'json');
        // récup la nationalité de l'objet json
        $nationalite = $nationaliteRepository->find($dataTab['nationalite']['id']);

        $serializer->deserialize($data,Auteur::class,'json',
            ['object_to_populate' => $auteur]);
        $auteur->setNationalite($nationalite); // si pas changée

        // Gestion des erreurs de validation
        $errors = $validator->validate($auteur);
        if (count($errors)) {
            $errorsJson->$serializer->serialize($errors, 'json');
            return new JsonResponse($errorsJson, Response::HTTP_BAD_REQUEST, [], true);
        }

        $manager->persist($auteur);
        $manager->flush();
//      1er arg. en général null car on n'affiche rien.
        return new JsonResponse("Auteur bien modifié",
            Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/auteurs/{id}", name="api_auteurs_delete", methods={"DELETE"})
     */
    public function delete(Auteur $auteur, ObjectManager $manager)
    {
        $manager->remove($auteur);
        $manager->flush();
        return new JsonResponse("Auteur bien supprimé",
            Response::HTTP_OK, []);
    }
}
