<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Entity\Entity;
use Doctrine\ORM\Query;


class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    /**
     * @Route("/dump_user", name="dump_users")
     */
    public function dump_user(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        # Getting Entity ID to make a relationship with User
        $entity[0] = $entityManager->getRepository(Entity::class)->find('1');
        $entity[1] = $entityManager->getRepository(Entity::class)->find('2');
        $entity[2] = $entityManager->getRepository(Entity::class)->find('3');

        //Sample User Data
        $users_data = [
            [
                'first_name' => 'Kathirmalan',
                'last_name' => 'Shanmugam',
                'age' => 29,
                'entity'=>[
                    $entity[0],
                    $entity[1]
                ]
            ],
            [
                'first_name' => 'Thomas',
                'last_name' => 'Igor',
                'age' => 32,
                'entity'=>[
                    $entity[1]
                ]
            ],
            [
                'first_name' => 'Alex',
                'last_name' => 'Parker',
                'age' => 33,
                'entity'=>[
                    $entity[1],
                    $entity[2]
                ]
            ],
            [
                'first_name' => 'Mathew',
                'last_name' => 'williams',
                'age' => 33,
                'entity'=>[
                    $entity[1],
                    $entity[2]
                ]
            ],
        ];

        $i = 0;
        foreach($users_data as $user){
            #Creating every new user object for each user and insert it
            $userObj = new User();
            $userObj->setFirstName($user['first_name']);
            $userObj->setLastName($user['last_name']);
            $userObj->setAge($user['age']);
            foreach($user['entity'] as $uEntity){
                $userObj->addUserEntityMap($uEntity);
            }

            $entityManager->persist($userObj);
            $entityManager->flush();
            $i++;
        }

        # Get the count of how many records avaible in the 'user' table
        $userRepo = $entityManager->getRepository(User::class);
        $total_users = $userRepo->createQueryBuilder('u')
                        ->select('count(u.id)')
                        ->getQuery()
                        ->getSingleScalarResult();

        return $this->json([
            'message' => 'Users Data has been imported',
            'total_users' => $total_users
        ]);
    }

    /**
     * @Route("/user_entity_relations", name="userEntityRelations")
     */
    public function userEntityRelations(): Response
    {
        $query = $this->getDoctrine()
                    ->getRepository(User::class) 
                    ->createQueryBuilder('u') 
                    ->getQuery(); 
        $user_list = $query->getArrayResult();

        $data = [];
        $conn = $this->getDoctrine()->getManager()->getConnection();
        foreach($user_list as $userRow)
        {
            $sql = 'SELECT (SELECT e.name FROM entity as e WHERE e.id=ue.entity_id) as entity_name,(SELECT e.department FROM entity as e WHERE e.id=ue.entity_id) as entity_department FROM user as u INNER JOIN user_entity as ue ON u.id = ue.user_id WHERE u.id = '.$userRow['id'];
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $userEntityArray = $stmt->fetchAll();

            $userRow_array = [
                'id' => $userRow['id'],
                'first_name' => $userRow['first_name'],
                'last_name' => $userRow['last_name'],
                'age' => $userRow['age'],
                'entity' => $userEntityArray
            ];
            array_push($data, $userRow_array);
        }
        $conn->close();

        return $this->json([
            'message' => 'API with list of entity by user ',
            'data' => $data
        ]);
    }

    /**
     * @Route("/user-list", name="userList")
     */
    public function userList(): Response
    {
        $query = $this->getDoctrine()
                    ->getRepository(User::class) 
                    ->createQueryBuilder('u') 
                    ->getQuery(); 
        $user_list = $query->getArrayResult();

        $data = [];
        foreach($user_list as $userRow)
        {
            $userRow_array = [
                'id' => $userRow['id'],
                'first_name' => $userRow['first_name'],
                'last_name' => $userRow['last_name'],
                'age' => $userRow['age']
            ];
            array_push($data, $userRow_array);
        }

        return $this->json([
            'message' => 'Showing the list of users',
            'data' => $data
        ]);
    }
}
