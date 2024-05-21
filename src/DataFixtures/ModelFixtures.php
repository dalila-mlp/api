<?php

namespace App\DataFixtures;

use App\Entity\ModelEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class ModelFixtures extends Fixture
{
    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 21; $i++) {
            $name = $this->faker->word;

            $model = new ModelEntity(
                id: $this->faker->unique()->numberBetween(1, 1000),
                filename: $name . '.py',
                name: $name,
                type: $this->faker->word,
                status: $this->faker->randomElement(['active', 'inactive', 'training']),
                uploadedAt: $this->faker->dateTimeThisYear,
                uploadedBy: $this->faker->name,
                weight: $this->faker->randomFloat(2, 0.1, 100.0),
                weightUnitSize: $this->faker->randomElement(['KB', 'MB', 'GB']),
                flops: $this->faker->randomFloat(2, 1, 10000),
                lastTrain: $this->faker->dateTimeThisMonth,
                deployed: $this->faker->boolean
            );

            $manager->persist($model);
        }

        $manager->flush();
    }
}
