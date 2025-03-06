<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Entity\Like;
use App\Repository\CommentsRepository;
use App\Repository\LikeRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class CommentsController extends AbstractController
{
    public function __construct(protected CommentsRepository $commentsRepository, protected EntityManagerInterface $em, protected string $defaultFrom)
    {}

    #[Route('/comments/{id}/edit', name: 'app_comments_edit')]
    public function edit($id, Request $request): Response
    {
        $currentRoute = $request->headers->get('referer');
        $comment = $this->commentsRepository->find($id);
        if (!$comment) {
            throw new NotFoundHttpException("Ce commentaire n'existe pas");
        }

        $this->denyAccessUnlessGranted('POST_EDIT', $comment, 'Vous n\'êtes pas l\'auteur de ce commentaire');

        if ($request->isMethod('POST')) {
            $content = trim($request->request->get('content'));
            if (empty($content)) {
                $this->addFlash('danger', 'Un commentaire ne peut pas être vide.');
                return $this->redirect($currentRoute);
            }
            $comment->setContent($content);
            $this->em->flush();

            $this->addFlash('success', 'Le commentaire a bien été modifié !');
        }

        return $this->redirect($currentRoute);
    }

    #[Route('comments/{id}/delete', name: 'app_comments_delete')]
    public function delete(Request $request, Comments $comments): Response
    {
        $currentRoute = $request->headers->get('referer');
        if ($this->isCsrfTokenValid('delete'.$comments->getId(), $request->request->get('_token'))) {
            $this->commentsRepository->remove($comments, true);
            $this->addFlash('success', 'Le commentaire a bien été supprimé !');
        }

        return $this->redirect($currentRoute);
    }

    #[Route('/comments/notify/{id}', name: 'app_comments_notify')]
    public function notify($id, Request $request, SendMailService $mail): Response
    {
        $currentRoute = $request->headers->get('referer');
        $comment = $this->commentsRepository->findOneBy(['id' => $id]);
        $mail->sendMail(
            null,
            'Notification pour un commentaire',
            $this->defaultFrom,
            'Demande de modération',
            'moderation',
            [
                'comment' => $comment
            ]
            );

        $this->addFlash('success', 'Votre demande de modération a bien été envoyé. Nous la traiterons dans les plus brefs délais.');

        return $this->redirect($currentRoute);
    }

    #[Route('/like-dislike-comment/{commentId}', name: 'app_comments_like', methods: ['POST'])]
    public function likeDislikeComment($commentId, LikeRepository $likeRepository)
    {
        $comment = $this->commentsRepository->find($commentId);
        if (!$comment) {
            return new JsonResponse(['message' => 'Comment not found'], 404);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['message' => 'Unauthorized'], 403);
        }

        // Check if the user has already liked the comment
        $existingLike = $comment->getLikes()->filter(function($like) use ($user) {
            return $like->getUser() === $user;
        })->first();

        if ($existingLike) {
            $this->em->remove($existingLike);
            $this->em->flush();
        } else {
            // Create a new Like entity
            $like = new Like;
            $like->setUser($user)
                ->setComment($comment);

            $this->em->persist($like);
            $this->em->flush();
        }
        
        $likeCount = $likeRepository->countLikesByComment($comment);

        return new JsonResponse(['message' => 'Like/Dislike updated', 'likeCount' => $likeCount]);
    }
}
