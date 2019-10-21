<?php

namespace App\Controller;

use App\Entity\Genre;
use App\Repository\GenreRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiGenreController extends AbstractController
{
    /**
     * @Route("/api/genres", name="api_genres", methods={"GET"})
     */
    public function list(GenreRepository $repo, SerializerInterface $serializer)
    {
        $genres = $repo->findAll();
        $resultat = $serializer->serialize(
          $genres,
          'json',
          [
              'groups' => ['ListeGenreFull']// Soit liste d'éléments à
              // sérialiser, soit @Groups ds Entity (éviter circular reference)
          ]
        );
        return new JsonResponse($resultat, 200, [], true);
    }

    /**
     * @Route("/api/genres/{id}", name="api_genres_show", methods={"GET"})
     */
    public function show(Genre $genre, SerializerInterface $serializer)
    {
        $resultat = $serializer->serialize(
            $genre,
            'json',
            [
                'groups' => ['ListeGenreSimple']
            ]
        );
//        return new JsonResponse($resultat, 200, [], true);
        return new JsonResponse($resultat, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/genres",
     *     name="api_genres_create",
     *     methods={"POST"})
     */
    public function create(Request $request,
                           ObjectManager $manager,
                           SerializerInterface $serializer)
    {
        $data = $request->getContent();
//        $genre = new Genre();
//        $serializer->deserialize(
//          $data,
//          Genre::class,
//          'json',
//          ['object_to_populate' => $genre]);

//        Solution du dessus, ou bien:
        $genre = $serializer->deserialize(
            $data,
            Genre::class,
            'json');

        $manager->persist($genre);
        $manager->flush();

// Pour une création pas de résultat donné ici (null, ou message).
// Dans [] = ce qu'on rajoute dans le header.
//        return new JsonResponse(
//            "Le genre a bien été créé.",
//            Response::HTTP_CREATED,
//            ["location" => "api/genres/" . $genre->getId()],
//            true);

//        Ou, pour renvoyer l'url absolue:
            return new JsonResponse(
                "Le genre a bien été créé.",
                Response::HTTP_CREATED,
            ["location" => $this->generateUrl(
                'api_genres_show',
                ["id" => $genre->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            )], true);
    }
}
