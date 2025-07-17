<?php

namespace App\Repository;

use Psr\Log\LoggerInterface;
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
    private $logger;
    public function __construct(
        ManagerRegistry $registry,
        LoggerInterface $logger
    ) {
        parent::__construct($registry, User::class);
        $this->logger = $logger;
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
        $users = $this->findBy(criteria: [], orderBy: ['username' => 'ASC']);

        usort(
            array: $users,
            callback: function ($a, $b): int {
                // Lower cases and normalize once
                $fullNameA = strtolower(string: $a->getUsername());
                $fullNameB = strtolower(string: $b->getUsername());

                // Parse names once and handle edge cases
                $namePartsA = $this->parseUserName($fullNameA);
                $namePartsB = $this->parseUserName($fullNameB);

                // Compare last names first
                $result = strcmp($namePartsA['lastname'], $namePartsB['lastname']);

                // If last names are equal, compare first names
                if ($result === 0) {
                    $result = strcmp($namePartsA['firstname'], $namePartsB['firstname']);
                }

                return $result;
            }
        );

        return $users;
    }

    /**
     * Parses a username into firstname and lastname components.
     *
     * Handles usernames in "firstname.lastname" format. If the format is unexpected
     * (no dot or only one part), uses the entire username as both firstname and lastname
     * to ensure consistent sorting behavior.
     *
     * @param string $username The username to parse
     * @return array{firstname: string, lastname: string} Associative array with firstname and lastname
     */
    private function parseUserName(string $username): array
    {
        $parts = explode('.', $username, 2); // Limit to 2 parts for efficiency
        
        if (count($parts) === 2) {
            return [
                'firstname' => $parts[0],
                'lastname' => $parts[1]
            ];
        }
        
        // Fallback: use the entire username as both first and last name
        // This ensures consistent sorting for usernames without dots
        return [
            'firstname' => $parts[0],
            'lastname' => $parts[0]
        ];
    }
}
