<?php

namespace Database\Factories;

use App\Models\GoogleDriveFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GoogleDriveFolder>
 */
class GoogleDriveFolderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'folder_id' => fake()->sha256(),
            'folder_name' => fake()->words(3, true),
            'folder_link' => 'https://drive.google.com/drive/folders/' . fake()->sha256(),
            'is_active' => true,
            'added_by' => fake()->email(),
        ];
    }
}
