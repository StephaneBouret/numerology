<?php

namespace App\Controller;

use App\Entity\Testimonial;
use App\Entity\User;
use App\Event\TestimonialSuccessEvent;
use App\Form\TestimonialFormType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TestimonialRepository;
use App\Service\RatingService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class TestimonialController extends AbstractController
{
    public function __construct(protected EntityManagerInterface $em, protected TestimonialRepository $testimonialRepository)
    {}

    #[Route('/testimonial/new', name: 'app_testimonial')]
    public function index(Request $request, EventDispatcherInterface $dispatcher, RatingService $ratingService): Response
    {
        $testimonials = $this->testimonialRepository->findBy(
            ['isApproved' => true],
            ['id' => 'DESC'],
            5
        );

        $avgRating = $this->testimonialRepository->getAvgRatings();
        $totalCount = $this->testimonialRepository->getTotalCount();
        $percentages = $ratingService->calculateRatingPercentages($this->testimonialRepository->findBy(['isApproved' => true]));

        /** @var User */
        $user = $this->getUser();
        $hasLeftTestimonial = $user ? !$user->getTestimonials()->isEmpty() : null;

        $testimonial = new Testimonial;
        $form = $this->createForm(TestimonialFormType::class, $testimonial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $testimonial->setIsApproved(false)
                    ->setAuthor($user);

            $this->em->persist($testimonial);
            $this->em->flush();

            $testimonialEvent = new TestimonialSuccessEvent($testimonial);
            $dispatcher->dispatch($testimonialEvent, 'testimonial.success');

            $this->addFlash('success', 'Votre témoignage a bien été envoyé et sera validé prochainement');
            return $this->redirectToRoute('home_index');
        }

        return $this->render('testimonial/index.html.twig', [
            'form' => $form,
            'testimonials' => $testimonials,
            'hasLeftTestimonial' => $hasLeftTestimonial,
            'avgRating' => $avgRating,
            'totalCount' => $totalCount,
            'percentages' => $percentages,
        ]);
    }
}
