<?php

namespace App\Service;

use MongoDB\Client;

class AnimalService
{
    private $mongoClient;

    public function __construct(Client $mongoClient)
    {
        $this->mongoClient = $mongoClient;
    }

    public function incrementConsultationCount(string $animalName): void
    {
        $db = $this->mongoClient->selectDatabase('ArcadiaZoo');
        $collection = $db->selectCollection('Animals');

        // Increment the consultation count of the specified animal
        $collection->updateOne(
            ['name' => $animalName], // Filter by animal name
            ['$inc' => ['consultationCount' => 1]] // Increment consultationCount by 1
        );
    }
}
