<?php

namespace App\Tests\Entity;

use App\Entity\Trip;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TripTest extends TestCase
{
    public function testTripCreation(): void
    {
        $trip = new Trip();

        $this->assertNull($trip->getId());
        $this->assertNull($trip->getName());
        $this->assertNull($trip->getDescription());
        $this->assertNull($trip->getStartDate());
        $this->assertNull($trip->getEndDate());
        $this->assertNull($trip->getDestination());
        $this->assertNull($trip->getBudget());
        $this->assertNull($trip->getUser());
        $this->assertNull($trip->getCreatedAt());
        $this->assertNull($trip->getUpdatedAt());
    }

    public function testTripSettersAndGetters(): void
    {
        $trip = new Trip();
        $user = new User();
        $startDate = new \DateTime('2024-07-01');
        $endDate = new \DateTime('2024-07-15');

        $trip->setName('Summer Vacation');
        $trip->setDescription('A wonderful summer trip');
        $trip->setStartDate($startDate);
        $trip->setEndDate($endDate);
        $trip->setDestination('Paris, France');
        $trip->setBudget('2500.00');
        $trip->setUser($user);

        $this->assertEquals('Summer Vacation', $trip->getName());
        $this->assertEquals('A wonderful summer trip', $trip->getDescription());
        $this->assertSame($startDate, $trip->getStartDate());
        $this->assertSame($endDate, $trip->getEndDate());
        $this->assertEquals('Paris, France', $trip->getDestination());
        $this->assertEquals('2500.00', $trip->getBudget());
        $this->assertSame($user, $trip->getUser());
    }

    public function testTripTimestamps(): void
    {
        $trip = new Trip();

        $this->assertNull($trip->getCreatedAt());
        $this->assertNull($trip->getUpdatedAt());

        // Simulate PrePersist lifecycle callback
        $trip->setCreatedAtValue();

        $this->assertInstanceOf(\DateTimeInterface::class, $trip->getCreatedAt());
        $this->assertInstanceOf(\DateTimeInterface::class, $trip->getUpdatedAt());

        $createdAt = $trip->getCreatedAt();
        $originalUpdatedAt = $trip->getUpdatedAt();

        // Wait a moment
        sleep(1);

        // Simulate PreUpdate lifecycle callback
        $trip->setUpdatedAtValue();

        $this->assertSame($createdAt, $trip->getCreatedAt(), 'CreatedAt should not change');
        $this->assertNotSame($originalUpdatedAt, $trip->getUpdatedAt(), 'UpdatedAt should change');
        $this->assertGreaterThan($originalUpdatedAt, $trip->getUpdatedAt());
    }

    public function testTripFluentInterface(): void
    {
        $trip = new Trip();
        $user = new User();

        $result = $trip
            ->setName('Test Trip')
            ->setDescription('Test Description')
            ->setDestination('Test Destination')
            ->setBudget('1000.00')
            ->setUser($user);

        $this->assertSame($trip, $result, 'Setters should return the entity instance for fluent interface');
    }

    public function testNullableBudget(): void
    {
        $trip = new Trip();

        $trip->setBudget(null);

        $this->assertNull($trip->getBudget(), 'Budget should be nullable');
    }

    public function testNullableDescription(): void
    {
        $trip = new Trip();

        $trip->setDescription(null);

        $this->assertNull($trip->getDescription(), 'Description should be nullable');
    }
}
