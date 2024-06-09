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
                filename: $name . '.py',
                name: $name,
                type: $this->faker->word,
                weight: $this->faker->randomFloat(2, 0.1, 100.0),
            );
            $model->setStatus($this->faker->randomElement(['active', 'inactive', 'training']));
            $model->setUploadedBy($this->faker->name);

            $manager->persist($model);
        }

        $manager->flush();
    }
}
