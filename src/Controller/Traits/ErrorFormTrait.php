<?php

namespace App\Controller\Traits;

use App\Entity\AppointmentType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

trait ErrorFormTrait
{
    private function renderAppointmentForm(
        FormInterface $form,
        AppointmentType $type,
        ?string $error = null,
        string $routeName = 'app_appointment_ajax_form'
    ): Response {
        if ($error) {
            $form->addError(new FormError($error));
        }

        return $this->render('appointment/_form.html.twig', [
            'form' => $form,
            'type' => $type,
            'action' => $this->generateUrl($routeName, ['id' => $type->getId()]),
        ]);
    }
}
