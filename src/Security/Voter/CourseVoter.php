<?php

namespace App\Security\Voter;

use App\Entity\Courses;
use App\Entity\Program;
use App\Entity\Purchase;
use App\Entity\Sections;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class CourseVoter extends Voter
{
    public const VIEW = 'VIEW_COURSE';
    public const COMMENT = 'COMMENT_COURSE';
    public const CHECK = 'CHECK_COURSE';
    public const SECTION_VIEW = 'SECTION_VIEW';
    public const PROGRAM_VIEW = 'PROGRAM_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::COMMENT, self::CHECK, self::PROGRAM_VIEW, self::SECTION_VIEW])
            && ($subject instanceof Courses || $subject instanceof Sections || $subject instanceof Program);
    }

    public function __construct(protected Security $security)
    {}

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return match($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::COMMENT => $this->canComment($subject, $user),
            self::CHECK => $this->canComment($subject, $user),
            self::SECTION_VIEW => $this->canViewSection($subject, $user),
            self::PROGRAM_VIEW => $this->canViewProgram($subject, $user),
            default => false
        };
    }

    private function canView(Courses $course, ?User $user): bool
    {
        // ✅ Un Admin ou un Guest peut voir le cours
        if ($this->hasElevatedAccess($user)) {
            return true;
        }

        // Si le cours est gratuit, accès pour tout le monde (même si un utilsateur est anonyme)
        if ($course->isFree()) {
            return true;
        }

        // Si l'utilisateur n'est pas connecté, il ne peut accéder qu'aux cours gratuits
        if (!$user) {
            return false;
        }

        // Vérifie si l'utilisateur a acheté ce programme avec un status PAYE
        return $user->getPurchases()->exists(
            fn ($key, Purchase $p) => $p->getProgram() === $course->getProgram() && $p->getStatus() === Purchase::STATUS_PAID
        );
    }

    private function canComment(Courses $course, ?User $user): bool
    {
        // ✅ Un admin ou un Guest peut toujours commenter
        if ($this->hasElevatedAccess($user)) {
            return true;
        }

        // ✅ Un utilisateur peut commenter seulement s'il a payé
        if (!$user) {
            return false;
        }

        return $user->getPurchases()->exists(
            fn ($key, Purchase $p) =>
            $p->getProgram() === $course->getProgram() && $p->getStatus() === Purchase::STATUS_PAID
        );
    }

    private function canViewSection(Sections $section, ?User $user): bool
    {
        // ✅ Si l'utilisateur est admin ou guest, il peut voir toutes les sections
        if ($this->hasElevatedAccess($user)) {
            return true;
        }

        // ✅ Vérifie si au moins un cours de la section est gratuit
        foreach ($section->getCourses() as $course) {
            if ($course->isFree()) {
                return true;
            }
        }

        // ✅ Si l'utilisateur est anonyme, il ne peut voir que les sections avec des cours gratuits
        if (!$user) {
            return false;
        }

        // ✅ Vérifie si l'utilisateur a payé pour un cours dans la section
        foreach ($user->getPurchases() as $purchase) {
            foreach ($section->getCourses() as $course) {
                if ($purchase->getProgram() === $course->getProgram() && $purchase->getStatus() === Purchase::STATUS_PAID) {
                    return true;
                }
            }
        }

        return false;
    }

    private function canViewProgram(Program $program, ?User $user): bool
    {
        if ($this->hasElevatedAccess($user)) {
            return true;
        }

        foreach ($program->getCourses() as $course) {
            if ($course->isFree()) {
                return true;
            }
        }

        if (!$user) {
            return false;
        }

        return $user->getPurchases()->exists(
            fn ($key, Purchase $p) =>
            $p->getProgram() === $program && $p->getStatus() === Purchase::STATUS_PAID
        );
    }

    private function hasElevatedAccess(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true) || in_array('ROLE_GUEST', $roles, true)) {
            return true;
        }

        return false;
    }
}
