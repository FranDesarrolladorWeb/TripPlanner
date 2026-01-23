<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DestinationApiControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testListDestinations(): void
    {
        $this->client->request('GET', '/api/destinations');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('destinations', $responseData);
        $this->assertIsArray($responseData['destinations']);
        $this->assertGreaterThan(0, count($responseData['destinations']));

        // Verify destination structure
        $destination = $responseData['destinations'][0];
        $this->assertArrayHasKey('id', $destination);
        $this->assertArrayHasKey('name', $destination);
        $this->assertArrayHasKey('country', $destination);
        $this->assertArrayHasKey('category', $destination);
        $this->assertArrayHasKey('rating', $destination);
        $this->assertArrayHasKey('reviews_count', $destination);
        $this->assertArrayHasKey('image_url', $destination);
        $this->assertArrayHasKey('description', $destination);
        $this->assertArrayHasKey('highlights', $destination);
    }

    public function testDestinationDataQuality(): void
    {
        $this->client->request('GET', '/api/destinations');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $destinations = $responseData['destinations'];

        foreach ($destinations as $destination) {
            // Verify rating is between 0 and 5
            $this->assertGreaterThanOrEqual(0, $destination['rating']);
            $this->assertLessThanOrEqual(5, $destination['rating']);

            // Verify reviews_count is positive
            $this->assertGreaterThan(0, $destination['reviews_count']);

            // Verify highlights is an array with items
            $this->assertIsArray($destination['highlights']);
            $this->assertGreaterThan(0, count($destination['highlights']));

            // Verify category is not empty
            $this->assertNotEmpty($destination['category']);

            // Verify description is not empty
            $this->assertNotEmpty($destination['description']);
        }
    }

    public function testShowDestination(): void
    {
        // First get all destinations to get a valid ID
        $this->client->request('GET', '/api/destinations');
        $listResponse = json_decode($this->client->getResponse()->getContent(), true);
        $firstDestinationId = $listResponse['destinations'][0]['id'];

        // Now get specific destination
        $this->client->request('GET', '/api/destinations/' . $firstDestinationId);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('destination', $responseData);

        $destination = $responseData['destination'];

        $this->assertArrayHasKey('id', $destination);
        $this->assertArrayHasKey('name', $destination);
        $this->assertArrayHasKey('country', $destination);
        $this->assertArrayHasKey('category', $destination);
        $this->assertArrayHasKey('rating', $destination);
        $this->assertArrayHasKey('reviews_count', $destination);
        $this->assertArrayHasKey('image_url', $destination);
        $this->assertArrayHasKey('description', $destination);
        $this->assertArrayHasKey('highlights', $destination);

        // Show endpoint includes additional details
        $this->assertArrayHasKey('best_time_to_visit', $destination);
        $this->assertArrayHasKey('average_cost_per_day', $destination);
        $this->assertArrayHasKey('currency', $destination);
    }

    public function testShowDestinationNotFound(): void
    {
        $this->client->request('GET', '/api/destinations/99999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($responseData['success']);
    }

    public function testDestinationsPublicAccess(): void
    {
        // Verify destinations endpoint is publicly accessible without authentication
        $this->client->request('GET', '/api/destinations');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
    }

    public function testFeaturedDestinations(): void
    {
        $this->client->request('GET', '/api/destinations');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $destinations = $responseData['destinations'];

        // Verify we have featured destinations from different categories
        $categories = array_unique(array_column($destinations, 'category'));

        $this->assertGreaterThan(1, count($categories), 'Should have destinations from multiple categories');

        // Verify popular destinations are included
        $destinationNames = array_column($destinations, 'name');

        // Check for some expected popular destinations
        $expectedDestinations = ['Paris', 'Tokyo', 'New York'];
        foreach ($expectedDestinations as $expectedName) {
            $this->assertContains(
                $expectedName,
                $destinationNames,
                "Featured destinations should include {$expectedName}"
            );
        }
    }
}
