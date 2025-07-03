<?php

namespace Tests\Feature\Admin;

use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\Text;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TextContentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        // Create an admin user for authentication
        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach(1); // Attach admin role
    }

    public function testAdminCanUpdateTextContent()
    {
        $module = Module::factory()->create();
        $text = Text::factory()->create();
        $moduleContent = ModuleContent::factory()->create([
            'module_id' => $module->id,
            'content_type' => 'text',
            'content_id' => $text->id,
            'order' => 1,
            'is_required' => true,
        ]);

        $updatedData = [
            'title' => 'Updated Title',
            'content' => 'Updated content for the text.',
            'format' => 'html',
            'order' => 2,
            'is_required' => false,
        ];

        $response = $this->actingAs($this->adminUser)->putJson(
            "/api/admin/modules/{$module->id}/contents/{$moduleContent->id}/texts/{$text->id}",
            $updatedData
        );

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Text content updated successfully.',
                'data' => [
                    'title' => 'Updated Title',
                    'content' => 'Updated content for the text.',
                    'format' => 'html',
                    'module_content' => [
                        'order' => 2,
                        'is_required' => false,
                        'title' => 'Updated Title',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('texts', [
            'id' => $text->id,
            'title' => 'Updated Title',
            'content' => 'Updated content for the text.',
            'format' => 'html',
        ]);

        $this->assertDatabaseHas('module_contents', [
            'id' => $moduleContent->id,
            'module_id' => $module->id,
            'content_id' => $text->id,
            'content_type' => 'text',
            'order' => 2,
            'is_required' => false,
            'title' => 'Updated Title',
        ]);
    }

    public function testUpdateTextContentValidation()
    {
        $module = Module::factory()->create();
        $text = Text::factory()->create();
        $moduleContent = ModuleContent::factory()->create([
            'module_id' => $module->id,
            'content_type' => 'text',
            'content_id' => $text->id,
            'order' => 1,
            'is_required' => true,
        ]);

        $invalidData = [
            'title' => '',
            'content' => '',
            'format' => 'invalid_format',
            'order' => 0,
            'is_required' => 'not_a_boolean',
        ];

        $response = $this->actingAs($this->adminUser)->putJson(
            "/api/admin/modules/{$module->id}/contents/{$moduleContent->id}/texts/{$text->id}",
            $invalidData
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content', 'format', 'order', 'is_required']);
    }

    public function testUpdateTextContentOrderUniqueness()
    {
        $module = Module::factory()->create();
        $text1 = Text::factory()->create();
        $text2 = Text::factory()->create();

        ModuleContent::factory()->create([
            'module_id' => $module->id,
            'content_type' => 'text',
            'content_id' => $text1->id,
            'order' => 1,
            'is_required' => true,
        ]);

        $moduleContent2 = ModuleContent::factory()->create([
            'module_id' => $module->id,
            'content_type' => 'text',
            'content_id' => $text2->id,
            'order' => 2,
            'is_required' => true,
        ]);

        $updatedData = [
            'title' => 'Updated Title',
            'content' => 'Updated content for the text.',
            'format' => 'markdown',
            'order' => 1, // Attempt to set to an existing order
            'is_required' => true,
        ];

        $response = $this->actingAs($this->adminUser)->putJson(
            "/api/admin/modules/{$module->id}/contents/{$moduleContent2->id}/texts/{$text2->id}",
            $updatedData
        );

        $response->assertStatus(500); // Expecting a 500 due to the Exception thrown by validateUniqueOrder
        $this->assertStringContainsString('The order has already been taken for this module.', $response->getContent());
    }
}
