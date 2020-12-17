<?php
namespace App\Security;

use App\Entity\Folder;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class FolderVoter extends Voter
{
    const OWNER = 'OWNER_OR_ADMIN';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if ($attribute != self::OWNER) {
            return false;
        }

        // only vote on `Folder` objects
        if (!$subject instanceof Folder) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        $folder = $subject;

        switch ($attribute) {
            case self::OWNER:
                return $user->canEdit($folder);
        }

        throw new \LogicException('This code should not be reached!');
    }

    
}
