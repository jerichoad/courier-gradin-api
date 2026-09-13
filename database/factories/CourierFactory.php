<?php

namespace Database\Factories;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    protected $model = Courier::class;

    public function definition(): array
    {
        return [
            'courier_code' => 'CR'.$this->faker->unique()->numberBetween(1, 999999),
            'courier_name' => $this->faker->name(),
            'courier_phone' => $this->faker->numerify('08##########'),
            'courier_email' => $this->faker->unique()->safeEmail(),
            'courier_level' => $this->faker->numberBetween(1, 5),
            'courier_address' => $this->faker->address(),
            'is_active' => true,
        ];
    }
}
