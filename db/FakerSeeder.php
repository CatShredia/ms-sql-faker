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

    public function GetDouble(float $min, float $max, int $precision = 8)
    {
        $factor = 10 ** $precision;
        $randomInt = random_int($min * $factor, $max * $factor);
        return $randomInt / $factor;
    }

    public function GetEmail()
    {
        return $this->faker->email();
    }

    public function GetName()
    {
        return $this->faker->name();
    }

    public function GetDateTime()
    {
        return $this->faker->dateTime()->format('Y-m-d-m-s');
    }

    public function GetDate()
    {
        return $this->faker->dateTime()->format('Y-m-d');
    }

    public function GetTime()
    {
        return $this->faker->time();
    }

    public function GetBoolean()
    {
        return $this->faker->boolean();
    }

    public function GetData($dataType)
    {
        $dataType = strtolower($dataType);

        if ($dataType == "int") {
            return $this->GetInt(0, 100);
        }
        if ($dataType == "varchar") {
            return $this->GetName();
        }
        if ($dataType == "decimal") {
            return $this->GetDouble(0, 100, 2);
        }
        if ($dataType == "datetime") {
            return $this->GetDateTime();
        }
        if ($dataType == "date") {
            return $this->GetDate();
        }
        if ($dataType == "time") {
            return $this->GetTime();
        }
        if ($dataType == "bit") {
            return $this->GetBoolean();
        }

        return "unkown_seed_type";
    }
}
