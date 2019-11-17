<?php
// src/Serializer/LivreContextBuilder.php

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\Livre;

final class LivreContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;
    private $authorizationChecker;

    public function __construct(SerializerContextBuilderInterface $decorated,
                                AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        $isManager = $this->authorizationChecker->isGranted('ROLE_MANAGER');
        if ($resourceClass === Livre::class && isset($context['groups'])
            && $isManager && true === $normalization) {
            $context['groups'][] = 'get_role_manager';
        }
        if ($resourceClass === Livre::class && isset($context['groups'])
            && $this->authorizationChecker->isGranted('ROLE_ADMIN')
            && false === $normalization) { // PUT/POST: dénormalisation (array => object)
                if ($request->getMethod() == 'put') {
                    $context['groups'][] = 'put_admin';
                }
        }
//dump($context);die();
//        dump($request->getMethod());die();
//        dump($this->authorizationChecker->isGranted('ROLE_ADHERENT'));die();
        return $context;
    }
}