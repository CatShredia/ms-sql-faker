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

        $typeMap = [
            'int' => fn() => $this->GetInt(0, 100),
            'varchar' => fn() => $this->GetName(),
            'nvarchar' => fn() => $this->GetName(),
            'decimal' => fn() => $this->GetDouble(0, 100, 2),
            'numeric' => fn() => $this->GetDouble(0, 100, 2),
            'datetime' => fn() => $this->GetDateTime(),
            'date' => fn() => $this->GetDate(),
            'time' => fn() => $this->GetTime(),
            'bit' => fn() => $this->GetBoolean(),
            'tinyint' => fn() => $this->GetBoolean(),
            'char' => fn() => substr($this->GetName(), 0, 1),
            'nchar' => fn() => substr($this->GetName(), 0, 1),
            'text' => fn() => $this->faker->sentence(),
            'uniqueidentifier' => fn() => $this->faker->uuid(),
            'float' => fn() => $this->GetDouble(0, 100, 2),
            'money' => fn() => $this->GetDouble(0, 1000, 2),
        ];

        if (isset($typeMap[$dataType])) {
            return $typeMap[$dataType]();
        }

        return "unknown_type: $dataType";
    }

    public function getFillType($dataType)
    {
        $dataType = strtolower($dataType);

        $fillTypeMap = [
            'int' => 'random_int',
            'varchar' => 'faker_name',
            'nvarchar' => 'faker_name',
            'decimal' => 'random_double',
            'numeric' => 'random_double',
            'datetime' => 'faker_datetime',
            'date' => 'faker_date',
            'time' => 'faker_time',
            'bit' => 'faker_boolean',
            'tinyint' => 'faker_boolean',
            'char' => 'faker_char',
            'nchar' => 'faker_char',
            'text' => 'faker_sentence',
            'uniqueidentifier' => 'faker_uuid',
            'float' => 'random_double',
            'money' => 'random_double',
        ];

        return $fillTypeMap[$dataType] ?? 'custom';
    }
}
