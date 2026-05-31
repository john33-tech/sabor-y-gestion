<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashClosureFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(['Open', 'Closed']);
        $initial = $this->faker->randomFloat(2, 500, 5000);
        $totalSales = null;
        $finalAmount = null;
        $totalCash = null;
        $totalCard = null;
        $totalQr = null;
        $difference = null;
        $closingDate = null;

        if ($status === 'Closed') {
            $totalSales = $this->faker->randomFloat(2, 100, 10000);
            $totalCash = $this->faker->randomFloat(2, 0, $totalSales);
            $totalCard = $this->faker->randomFloat(2, 0, $totalSales - $totalCash);
            $totalQr = $totalSales - $totalCash - $totalCard;
            $finalAmount = $initial + $totalSales;
            $difference = $this->faker->randomFloat(2, -50, 50);
            $closingDate = $this->faker->dateTimeBetween('-30 days', 'now');
        }

        $openingDate = $status === 'Closed'
            ? $this->faker->dateTimeBetween($closingDate, '-1 hour')
            : $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'user_id' => User::factory(),
            'initial_amount' => $initial,
            'final_amount' => $finalAmount,
            'total_sales' => $totalSales,
            'total_cash' => $totalCash,
            'total_card' => $totalCard,
            'total_qr' => $totalQr,
            'difference' => $difference,
            'opening_date' => $openingDate,
            'closing_date' => $closingDate,
            'status' => $status,
            'observations' => $status === 'Closed' ? $this->faker->sentence() : null,
        ];
    }
}
