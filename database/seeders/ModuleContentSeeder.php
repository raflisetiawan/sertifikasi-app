<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\Text;
use App\Models\Video;
use App\Models\File;
use App\Models\Quiz;
use App\Models\Assignment;
use App\Models\Practice;
use Illuminate\Database\Seeder;

class ModuleContentSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }

    public function run()
    {
        $modules = Module::all();

        if ($modules->isEmpty()) {
            $this->command->info('No modules found, please seed modules first.');
            return;
        }

        foreach ($modules as $module) {
            $this->command->info("Seeding content for module: {$module->title} (ID: {$module->id})");

            // Create 2-5 contents per module
            $numberOfContents = $this->faker->numberBetween(2, 5);
            for ($i = 0; $i < $numberOfContents; $i++) {
                $contentType = $this->faker->randomElement(['text', 'video', 'file', 'quiz', 'assignment', 'practice']);
                $content = null;

                switch ($contentType) {
                    case 'text':
                        $content = Text::factory()->create();
                        break;
                    case 'video':
                        $content = Video::factory()->create();
                        break;
                    case 'file':
                        $content = File::factory()->create();
                        break;
                    case 'quiz':
                        $content = Quiz::factory()->create();
                        break;
                    case 'assignment':
                        $content = Assignment::factory()->create();
                        break;
                    case 'practice':
                        $content = Practice::factory()->create();
                        break;
                }

                if ($content) {
                    ModuleContent::factory()->create([
                        'module_id' => $module->id,
                        'title' => $content->title,
                        'content_type' => $contentType,
                        'content_id' => $content->id,
                        'order' => $i + 1,
                        'is_required' => $this->faker->boolean(80),
                        'minimum_duration_seconds' => ($contentType === 'video') ? $content->duration_seconds * 0.9 : null,
                    ]);
                }
            }
        }
    }
}
