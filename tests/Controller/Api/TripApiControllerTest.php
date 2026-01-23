<?php

namespace App\Tests\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TripApiControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function createUserAndGetToken(): string
    {
        $email = 'test_' . uniqid() . '@example.com';

        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'password123'
        ]));

        $response = json_decode($this->client->getResponse()->getContent(), true);
        return $response['token'];
    }

    public function testCreateTripSuccess(): void
    {
        $token = $this->createUserAndGetToken();

        $this->client->request('POST', '/api/trips', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Summer Vacation',
            'destination' => 'Paris, France',
            'start_date' => '2024-07-01 00:00:00',
            'end_date' => '2024-07-15 00:00:00',
            'description' => 'Romantic summer trip',
            'budget' => '2500.00'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('trip', $responseData);
        $this->assertEquals('Summer Vacation', $responseData['trip']['name']);
        $this->assertEquals('Paris, France', $responseData['trip']['destination']);
        $this->assertEquals('2500.00', $responseData['trip']['budget']);
    }

    public function testCreateTripMissingFields(): void
    {
        // Re-register to get a fresh token for this test
        $email = 'missing_fields_' . time() . '@example.com';
        $this->client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => $email,
            'password' => 'password123'
        ]));
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $token = $response['token'];

        $this->client->request('POST', '/api/trips', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Incomplete Trip'
            // Missing required fields: destination, start_date, end_date
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($responseData['success']);
    }

    public function testCreateTripUnauthorized(): void
    {
        $this->client->request('POST', '/api/trips', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Unauthorized Trip',
            'destination' => 'Tokyo, Japan',
            'start_date' => '2024-08-01 00:00:00',
            'end_date' => '2024-08-15 00:00:00'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testListTrips(): void
    {
        $token = $this->createUserAndGetToken();

        // Create two trips
        $this->client->request('POST', '/api/trips', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Trip 1',
            'destination' => 'New York',
            'start_date' => '2024-06-01 00:00:00',
            'end_date' => '2024-06-10 00:00:00'
        ]));

        $this->client->request('POST', '/api/trips', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Trip 2',
            'destination' => 'Tokyo',
            'start_date' => '2024-09-01 00:00:00',
            'end_date' => '2024-09-15 00:00:00'
        ]));

        // List trips
        $this->client->request('GET', '/api/trips', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('trips', $responseData);
        $this->assertCount(2, $responseData['trips']);

        // Should be ordered by start_date DESC (Trip 2 first)
        $this->assertEquals('Trip 2', $responseData['trips'][0]['name']);
        $this->assertEquals('Trip 1', $responseData['trips'][1]['name']);
    }

    public function testShowTrip(): void
    {
        $token = $this->createUserAndGetToken();

        // Create a trip
        $this->client->request('POST', '/api/trips', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Show Test Trip',
            'destination' => 'Barcelona',
            'start_date' => '2024-05-01 00:00:00',
            'end_date' => '2024-05-10 00:00:00'
        ]));

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $tripId = $createResponse['trip']['id'];

        // Get trip details
        $this->client->request('GET', '/api/trips/' . $tripId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('trip', $responseData);
        $this->assertEquals('Show Test Trip', $responseData['trip']['name']);
        $this->assertEquals('Barcelona', $responseData['trip']['destination']);
    }

    public function testShowTripNotFound(): void
    {
        $token = $this->createUserAndGetToken();

        $this->client->request('GET', '/api/trips/99999', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateTrip(): void
    {
        $token = $this->createUserAndGetToken();

        // Create a trip
        $this->client->request('POST', '/api/trips', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Original Trip',
            'destination' => 'Rome',
            'start_date' => '2024-10-01 00:00:00',
            'end_date' => '2024-10-10 00:00:00',
            'budget' => '1500.00'
        ]));

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $tripId = $createResponse['trip']['id'];

        // Update the trip
        $this->client->request('PUT', '/api/trips/' . $tripId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Updated Trip',
            'budget' => '2000.00'
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals('Updated Trip', $responseData['trip']['name']);
        $this->assertEquals('2000.00', $responseData['trip']['budget']);
        $this->assertEquals('Rome', $responseData['trip']['destination']); // Unchanged field
    }

    public function testDeleteTrip(): void
    {
        $token = $this->createUserAndGetToken();

        // Create a trip
        $this->client->request('POST', '/api/trips', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Trip to Delete',
            'destination' => 'Dubai',
            'start_date' => '2024-11-01 00:00:00',
            'end_date' => '2024-11-10 00:00:00'
        ]));

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $tripId = $createResponse['trip']['id'];

        // Delete the trip
        $this->client->request('DELETE', '/api/trips/' . $tripId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);

        // Verify it's deleted
        $this->client->request('GET', '/api/trips/' . $tripId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testUserCannotAccessOtherUsersTrips(): void
    {
        // Create first user and trip
        $token1 = $this->createUserAndGetToken();

        $this->client->request('POST', '/api/trips', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token1,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'User 1 Trip',
            'destination' => 'London',
            'start_date' => '2024-12-01 00:00:00',
            'end_date' => '2024-12-10 00:00:00'
        ]));

        $createResponse = json_decode($this->client->getResponse()->getContent(), true);
        $tripId = $createResponse['trip']['id'];

        // Create second user
        $token2 = $this->createUserAndGetToken();

        // Try to access first user's trip with second user's token
        $this->client->request('GET', '/api/trips/' . $tripId, [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token2,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
