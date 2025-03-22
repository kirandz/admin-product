<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');

        $name = $faker->name;

        return [
            'name' => $name,
            'email' => $this->generateEmailFromName($name),
            'phone' => $faker->phoneNumber,
            'address' => $faker->address,
        ];
    }

    /**
     * Generate an email address based on the given name.
     *
     * @param string $name
     * @return string
     */
    protected function generateEmailFromName(string $name): string
    {
        $nameParts = explode(' ', strtolower($name));
        $email = implode('.', $nameParts) . '@example.com';
        return $email;
    }
}
