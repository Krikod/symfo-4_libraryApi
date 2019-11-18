<?php

namespace App\Services;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Pret;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

class PretSubscriber implements EventSubscriberInterface
{
    private $token;

    public function __construct(TokenStorageInterface $token)
    {
        $this->token = $token;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['getAuthenticatedUser',
                EventPriorities::PRE_WRITE]
        ];
    }

    public function getAuthenticatedUser(ViewEvent $event)
    {
        $entity = $event->getControllerResult(); // Récupère l'entité qui a déclenché l'événement
        $method = $event->getRequest()->getMethod(); // Récupère la méthode invoquée dans la request
        $adherent = $this->token->getToken()->getUser(); // Récupère l'adhérent actuellement connecté qui a lancé la request
        if ($entity->instanceof(Pret::class) && $method == Request::METHOD_POST) { // Si c'est une meth POST sur une instance de Pret
            $entity->setAdherent($adherent); // On affecte l'adhérent à la prop adherent de pret
        }
        return;
    }
}