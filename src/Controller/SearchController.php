<?php

namespace App\Controller;

use App\Entity\Courses;
use App\Form\CourseAutocompleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SearchController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/search', name: 'search')]
    public function searchBar(Request $request): Response
    {
        $form = $this->createForm(CourseAutocompleteType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->get('name')->getData();
            $slug = $data->getSlug();
            $section = $data->getSection()->getSlug();
            $program = $data->getProgram()->getSlug();

            return $this->redirectToRoute('courses_show', ['program_slug' => $program, 'section_slug' => $section, 'slug' => $slug]);
        }

        return $this->render('partials/_search.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/course/details/{id}', name: 'course_details', methods: ['GET'])]
    public function getCourseDetails(Courses $course): JsonResponse
    {
        $data = [
            'program_slug' => $course->getProgram()->getSlug(),
            'section_slug' => $course->getSection()->getSlug(),
            'slug' => $course->getSlug(),
        ];

        return new JsonResponse($data);
    }
}