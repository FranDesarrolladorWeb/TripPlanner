<?php

namespace App\Controller\Api;

use App\Entity\Trip;
use App\Entity\User;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/trips', name: 'api_trips_')]
class TripApiController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TripRepository $tripRepository,
        private ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $trips = $this->tripRepository->findBy(
            ['user' => $user],
            ['startDate' => 'DESC']
        );

        $data = array_map(function (Trip $trip) {
            return [
                'id' => $trip->getId(),
                'name' => $trip->getName(),
                'description' => $trip->getDescription(),
                'destination' => $trip->getDestination(),
                'start_date' => $trip->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $trip->getEndDate()->format('Y-m-d H:i:s'),
                'budget' => $trip->getBudget(),
                'created_at' => $trip->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $trip->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $trips);

        return $this->json([
            'success' => true,
            'trips' => $data
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $trip = $this->tripRepository->find($id);

        if (!$trip) {
            return $this->json([
                'success' => false,
                'message' => 'Trip not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Ensure user can only access their own trips
        if ($trip->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json([
            'success' => true,
            'trip' => [
                'id' => $trip->getId(),
                'name' => $trip->getName(),
                'description' => $trip->getDescription(),
                'destination' => $trip->getDestination(),
                'start_date' => $trip->getStartDate()->format('Y-m-d H:i:s'),
                'end_date' => $trip->getEndDate()->format('Y-m-d H:i:s'),
                'budget' => $trip->getBudget(),
                'created_at' => $trip->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $trip->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['destination']) || !isset($data['start_date']) || !isset($data['end_date'])) {
            return $this->json([
                'success' => false,
                'message' => 'Missing required fields: name, destination, start_date, end_date'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $trip = new Trip();
            $trip->setName($data['name']);
            $trip->setDestination($data['destination']);
            $trip->setStartDate(new \DateTime($data['start_date']));
            $trip->setEndDate(new \DateTime($data['end_date']));
            $trip->setUser($user);

            if (isset($data['description'])) {
                $trip->setDescription($data['description']);
            }

            if (isset($data['budget'])) {
                $trip->setBudget($data['budget']);
            }

            // Validate
            $errors = $this->validator->validate($trip);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($trip);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Trip created successfully',
                'trip' => [
                    'id' => $trip->getId(),
                    'name' => $trip->getName(),
                    'description' => $trip->getDescription(),
                    'destination' => $trip->getDestination(),
                    'start_date' => $trip->getStartDate()->format('Y-m-d H:i:s'),
                    'end_date' => $trip->getEndDate()->format('Y-m-d H:i:s'),
                    'budget' => $trip->getBudget(),
                    'created_at' => $trip->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $trip->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error creating trip: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $trip = $this->tripRepository->find($id);

        if (!$trip) {
            return $this->json([
                'success' => false,
                'message' => 'Trip not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Ensure user can only update their own trips
        if ($trip->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        try {
            if (isset($data['name'])) {
                $trip->setName($data['name']);
            }

            if (isset($data['description'])) {
                $trip->setDescription($data['description']);
            }

            if (isset($data['destination'])) {
                $trip->setDestination($data['destination']);
            }

            if (isset($data['start_date'])) {
                $trip->setStartDate(new \DateTime($data['start_date']));
            }

            if (isset($data['end_date'])) {
                $trip->setEndDate(new \DateTime($data['end_date']));
            }

            if (isset($data['budget'])) {
                $trip->setBudget($data['budget']);
            }

            // Validate
            $errors = $this->validator->validate($trip);
            if (count($errors) > 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => (string) $errors
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Trip updated successfully',
                'trip' => [
                    'id' => $trip->getId(),
                    'name' => $trip->getName(),
                    'description' => $trip->getDescription(),
                    'destination' => $trip->getDestination(),
                    'start_date' => $trip->getStartDate()->format('Y-m-d H:i:s'),
                    'end_date' => $trip->getEndDate()->format('Y-m-d H:i:s'),
                    'budget' => $trip->getBudget(),
                    'created_at' => $trip->getCreatedAt()->format('Y-m-d H:i:s'),
                    'updated_at' => $trip->getUpdatedAt()->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error updating trip: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $trip = $this->tripRepository->find($id);

        if (!$trip) {
            return $this->json([
                'success' => false,
                'message' => 'Trip not found'
            ], Response::HTTP_NOT_FOUND);
        }

        // Ensure user can only delete their own trips
        if ($trip->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Access denied'
            ], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($trip);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Trip deleted successfully'
        ]);
    }
}
