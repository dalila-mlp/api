<?php

namespace App\DataFixtures;

use App\Entity\DatafileEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class DatafileFixtures extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 6; $i++) {
            $name = $this->faker->word;

            $datafile = new DatafileEntity(
                id: $this->faker->unique()->numberBetween(1, 1000),
                filename: $name . '.parquet',
                name: $name,
                type: $this->faker->word,
                status: $this->faker->randomElement(['active', 'inactive']),
                uploadedAt: $this->faker->dateTimeThisYear,
                uploadedBy: $this->faker->name,
                weight: $this->faker->randomFloat(2, 0.1, 100.0),
                weightUnitSize: $this->faker->randomElement(['KB', 'MB', 'GB']),
            );

            $manager->persist($datafile);
        }

        $manager->flush();
    }
}
