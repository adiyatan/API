<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MemberTubesSeeder::class);
        $this->call(DogSeeder::class);
        $this->call(BookstubesSeeder::class);
    }
}
