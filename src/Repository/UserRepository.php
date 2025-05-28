<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends BaseRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Saves a User entity to the database.
     *
     * @param User $entity The User entity to be saved
     * @param bool $flush  Whether to immediately execute the persist query (true) or delay it (false)
     *
     * @return void
     */
    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * Removes a User entity from the database.
     *
     * @param User $entity The User entity to be removed
     * @param bool $flush  Whether to immediately execute the removal query (true) or delay it (false)
     *
     * @return void
     */
    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     *
     * This method is required by the PasswordUpgraderInterface and is used
     * by the password migration system to update password hashes to newer algorithms.
     *
     * @param PasswordAuthenticatedUserInterface $user               The user whose password needs to be upgraded
     * @param string                             $newHashedPassword  The new hashed password
     *
     * @throws UnsupportedUserException If the user is not an instance of App\Entity\User
     *
     * @return void
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }




    /**
     * Sorts an array of User objects by their last name, then by first name.
     * 
     * This function assumes usernames are in the format "firstname.lastname" and
     * sorts them alphabetically by lastname first, then by firstname if lastnames are identical.
     * If the username format is unexpected, it falls back to comparing the full usernames.
     *
     * @param User[] $users An array of User objects to be sorted
     *
     * @return User[] The sorted array of User objects
     */
    public function getAllUsersOrderedByLastname(): array
    {

        $users = $this->findBy([], ['username' => 'ASC']);

        usort(
            $users,
            function ($a, $b) {
                $result = 0;
                // Lower cases
                $fullNameA = strtolower($a->getUsername());
                $fullNameB = strtolower($b->getUsername());

                try {
                    // Split names to separate first name and last name
                    list($firstNameA, $lastNameA) = explode('.', $fullNameA);
                    list($firstNameB, $lastNameB) = explode('.', $fullNameB);

                    // Compare last names
                    $result = strcmp($lastNameA, $lastNameB);

                    // If last names are equal, then compare first names
                    if ($result == 0) {
                        $result = strcmp($firstNameA, $firstNameB);
                    }
                } catch (\Exception $e) {
                    // Fallback if name format is unexpected
                    $result = strcmp($fullNameA, $fullNameB);
                }

                return $result;
            }
        );

        return $users;
    }
}
