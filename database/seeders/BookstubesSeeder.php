<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookstubesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 10; $i++) {
            DB::table('bookstubes')->insert([
                'name' => $faker->sentence(3), // Generates a book name with 3 words
                'author' => $faker->name, // Generates an author name
                'time_release' => $faker->date(), // Generates a release date
                'stock' => $faker->numberBetween(1, 100), // Generates a stock number between 1 and 100
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
