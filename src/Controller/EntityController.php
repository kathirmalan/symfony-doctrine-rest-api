<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Entity;
date_default_timezone_set("Europe/Paris");

class EntityController extends AbstractController
{
    /**
     * @Route("/entity", name="entity")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/EntityController.php',
        ]);
    }

    /**
     * @Route("/dump_entity", name="dump_entity")
     */
    public function dump_user(): Response
    {
        $users_data = [
            [
                'name' => 'UI/UX developer',
                'department' => 'Software Development'
            ],
            [
                'name' => 'Backend Developer',
                'department' => 'Software Development'
            ],
            [
                'name' => 'Quality Analyst',
                'department' => 'Testing'
            ],
        ];

        $entityManager = $this->getDoctrine()->getManager();

        foreach($users_data as $user){
            $entityObj = new Entity();
            $entityObj->setName($user['name']);
            $entityObj->setDepartment($user['department']);
            
            $entityManager->persist($entityObj);
            $entityManager->flush();
        }

        $entityRepo = $entityManager->getRepository(Entity::class);

        $total_entities = $entityRepo->createQueryBuilder('e')
                        ->select('count(e.id)')
                        ->getQuery()
                        ->getSingleScalarResult();


        return $this->json([
            'message' => 'Entity Data has been imported',
            'total_entities' => $total_entities,
            'end_point_hit_time' => date("H:i:s")
        ]);
    }

    /**
     * @Route("/entity-list", name="entityList")
     */
    public function entityList(): Response
    {
        $query = $this->getDoctrine()
                    ->getRepository(Entity::class) 
                    ->createQueryBuilder('u') 
                    ->getQuery(); 
        $entity_list = $query->getArrayResult();

        $data = [];
        foreach($entity_list as $entityRow)
        {
            $entityRow_array = [
                'id' => $entityRow['id'],
                'name' => $entityRow['name'],
                'department' => $entityRow['department'],
            ];
            array_push($data, $entityRow_array);
        }

        return $this->json([
            'message' => 'Showing the list of Entity',
            'data' => $data
        ]);
    }
}
