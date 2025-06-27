<?php

namespace Db;

use Faker\Factory;

class FakerSeeder
{

    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function GetInt($firstNumber, $secondNumber)
    {
        return random_int($firstNumber, $secondNumber);
    }

    public function GetDoube(float $min, float $max, int $precision = 8)
    {
        $factor = 10 ** $precision;
        $randomInt = random_int($min * $factor, $max * $factor);
        return $randomInt / $factor;
    }

    public function GetEmail()
    {
        $this->faker->email();
    }
}
