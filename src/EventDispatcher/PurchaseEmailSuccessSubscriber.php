<?php

namespace App\EventDispatcher;

use App\Entity\User;
use App\Service\SendMailService;
use App\Event\PurchaseSuccessEvent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PurchaseEmailSuccessSubscriber implements EventSubscriberInterface
{
    public function __construct(protected SendMailService $sendMail, protected Security $security)
    {}

    public static function getSubscribedEvents(): array{
        return [
            'purchase.success' => 'sendSuccessEmail'
        ];
    }

    public function sendSuccessEmail(PurchaseSuccessEvent $purchaseSuccessEvent)
    {
        /** @var User */
        $currentUser = $this->security->getUser();

        $purchase = $purchaseSuccessEvent->getPurchase();

        $this->sendMail->sendMail(
            null,
            "Votre commande n°{$purchase->getNumber()}",
            $currentUser->getEmail(),
            "Bravo votre commande n°{$purchase->getNumber()} a bien été confirmée",
            "purchase_success",
            [
                'purchase' => $purchase,
                'user' => $currentUser,
            ]
        );
    }
}