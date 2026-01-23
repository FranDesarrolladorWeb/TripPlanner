<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/destinations', name: 'api_destinations_')]
class DestinationApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Static featured destinations for MVP
        // TODO: Move to database in future iteration
        $destinations = [
            [
                'id' => 1,
                'name' => 'Paris',
                'country' => 'France',
                'category' => 'City Break',
                'rating' => 4.8,
                'reviews_count' => 3200,
                'image_url' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800',
                'description' => 'The City of Light beckons with its iconic landmarks, world-class museums, and romantic atmosphere.',
                'highlights' => ['Eiffel Tower', 'Louvre Museum', 'Notre-Dame Cathedral', 'Champs-Élysées']
            ],
            [
                'id' => 2,
                'name' => 'Tokyo',
                'country' => 'Japan',
                'category' => 'Urban',
                'rating' => 4.9,
                'reviews_count' => 5100,
                'image_url' => 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800',
                'description' => 'A mesmerizing blend of ancient tradition and cutting-edge technology in the heart of Japan.',
                'highlights' => ['Shibuya Crossing', 'Senso-ji Temple', 'Tokyo Skytree', 'Meiji Shrine']
            ],
            [
                'id' => 3,
                'name' => 'Santorini',
                'country' => 'Greece',
                'category' => 'Beach',
                'rating' => 4.7,
                'reviews_count' => 2800,
                'image_url' => 'https://images.unsplash.com/photo-1613395877344-13d4a8e0d49e?w=800',
                'description' => 'Stunning white-washed buildings perched on volcanic cliffs overlooking the Aegean Sea.',
                'highlights' => ['Oia Sunset', 'Red Beach', 'Ancient Akrotiri', 'Wine Tasting']
            ],
            [
                'id' => 4,
                'name' => 'New York',
                'country' => 'USA',
                'category' => 'Urban',
                'rating' => 4.6,
                'reviews_count' => 4500,
                'image_url' => 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800',
                'description' => 'The city that never sleeps offers endless entertainment, culture, and iconic landmarks.',
                'highlights' => ['Statue of Liberty', 'Central Park', 'Times Square', 'Brooklyn Bridge']
            ],
            [
                'id' => 5,
                'name' => 'Bali',
                'country' => 'Indonesia',
                'category' => 'Beach',
                'rating' => 4.8,
                'reviews_count' => 3900,
                'image_url' => 'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=800',
                'description' => 'Tropical paradise with pristine beaches, ancient temples, and lush rice terraces.',
                'highlights' => ['Ubud Rice Terraces', 'Tanah Lot Temple', 'Seminyak Beach', 'Sacred Monkey Forest']
            ],
            [
                'id' => 6,
                'name' => 'Barcelona',
                'country' => 'Spain',
                'category' => 'City Break',
                'rating' => 4.7,
                'reviews_count' => 3600,
                'image_url' => 'https://images.unsplash.com/photo-1583422409516-2895a77efded?w=800',
                'description' => 'Vibrant Mediterranean city famous for Gaudí\'s architecture, beaches, and tapas culture.',
                'highlights' => ['Sagrada Família', 'Park Güell', 'Las Ramblas', 'Gothic Quarter']
            ],
            [
                'id' => 7,
                'name' => 'Dubai',
                'country' => 'UAE',
                'category' => 'Urban',
                'rating' => 4.6,
                'reviews_count' => 4200,
                'image_url' => 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800',
                'description' => 'Futuristic city with record-breaking architecture, luxury shopping, and desert adventures.',
                'highlights' => ['Burj Khalifa', 'Dubai Mall', 'Palm Jumeirah', 'Desert Safari']
            ],
            [
                'id' => 8,
                'name' => 'Rome',
                'country' => 'Italy',
                'category' => 'City Break',
                'rating' => 4.8,
                'reviews_count' => 4800,
                'image_url' => 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=800',
                'description' => 'The Eternal City where ancient ruins meet modern Italian life and incredible cuisine.',
                'highlights' => ['Colosseum', 'Vatican City', 'Trevi Fountain', 'Pantheon']
            ]
        ];

        return $this->json([
            'success' => true,
            'destinations' => $destinations
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        // For MVP, we'll return the destination from the static list
        // TODO: Move to database in future iteration
        $destinations = [
            1 => [
                'id' => 1,
                'name' => 'Paris',
                'country' => 'France',
                'category' => 'City Break',
                'rating' => 4.8,
                'reviews_count' => 3200,
                'image_url' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800',
                'description' => 'The City of Light beckons with its iconic landmarks, world-class museums, and romantic atmosphere.',
                'highlights' => ['Eiffel Tower', 'Louvre Museum', 'Notre-Dame Cathedral', 'Champs-Élysées'],
                'best_time_to_visit' => 'April to June, September to November',
                'average_cost_per_day' => 150,
                'currency' => 'EUR'
            ],
            2 => [
                'id' => 2,
                'name' => 'Tokyo',
                'country' => 'Japan',
                'category' => 'Urban',
                'rating' => 4.9,
                'reviews_count' => 5100,
                'image_url' => 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800',
                'description' => 'A mesmerizing blend of ancient tradition and cutting-edge technology in the heart of Japan.',
                'highlights' => ['Shibuya Crossing', 'Senso-ji Temple', 'Tokyo Skytree', 'Meiji Shrine'],
                'best_time_to_visit' => 'March to May, September to November',
                'average_cost_per_day' => 120,
                'currency' => 'JPY'
            ],
            // Add more destinations as needed
        ];

        if (!isset($destinations[$id])) {
            return $this->json([
                'success' => false,
                'message' => 'Destination not found'
            ], 404);
        }

        return $this->json([
            'success' => true,
            'destination' => $destinations[$id]
        ]);
    }
}
