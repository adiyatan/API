<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use App\Models\Dog;

class DogSeeder extends Seeder
{
    public function run()
    {
        $response = Http::withoutVerifying()->get('https://random.dog/woof.json');
        $data = $response->json();
        $types = [
            'French Bulldog',
            'Labrador Retriever',
            'Golden Retriever',
            'German Shepherd',
            'Poodle',
            'Bulldog',
            'Rottweiler',
            'Beagle',
            'Dachshund',
            'German Shorthaired Pointer',
            'Pembroke Welsh Corgi',
            'Australian Shepherd',
            'Yorkshire Terrier',
            'Cavalier King Charles Spaniel',
            'Doberman Pinscher',
            'Boxer',
            'Miniature Schnauzer',
            'Cane Corso',
            'Great Dane',
            'Shih Tzu',
        ];

        $favFoods = ['Kibble/Dry', 'Canned', 'Semi-Moist', 'Home Cooked', 'Raw'];

        for ($i = 1; $i <= 30; $i++) {
            // Fetch random dog images from https://random.dog/woof.json
            $response = Http::withoutVerifying()->get('https://random.dog/woof.json');
            $data = $response->json();

            // Generate a random name
            $name = $this->generateRandomName();

            // Select a random type from the provided list
            $type = $types[array_rand($types)];

            // Select a random favorite food from the provided list
            $favFood = $favFoods[array_rand($favFoods)];

            // Create a sample dog record using the fetched image
            Dog::create([
                'image' => $data['url'],
                'name' => $name,
                'type' => $type,
                'favFood' => $favFood,
                'description' => 'Description for Dog ' . $i,
            ]);
        }
    }

    private function generateRandomName()
    {
        $adjectives = ['Happy', 'Silly', 'Loyal', 'Clever', 'Adventurous', 'Playful', 'Gentle', 'Energetic', 'Graceful'];
        $nouns = ['Buddy', 'Charlie', 'Max', 'Lucy', 'Daisy', 'Rocky', 'Bailey', 'Zoe', 'Oliver', 'Luna'];

        $randomAdjective = $adjectives[array_rand($adjectives)];
        $randomNoun = $nouns[array_rand($nouns)];

        return $randomAdjective . ' ' . $randomNoun;
    }
}
