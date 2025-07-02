<?php

namespace App\Services;

use App\Models\Text;
use App\Models\ModuleContent;
use Illuminate\Support\Facades\DB;
use Exception;

class TextManagementService
{
    public function getAllTexts()
    {
        return Text::orderBy('created_at', 'desc')->get();
    }

    public function createText(array $data): Text
    {
        return DB::transaction(function () use ($data) {
            $text = Text::create([
                'title' => $data['title'],
                'content' => $data['content'],
                'format' => $data['format']
            ]);

            ModuleContent::create([
                'module_id' => $data['module_id'],
                'title' => $data['title'],
                'content_type' => 'text',
                'content_id' => $text->id,
                'order' => $data['order'],
                'is_required' => $data['is_required'] ?? true
            ]);

            return $text->load('moduleContent');
        });
    }

    public function getTextById(int $id): Text
    {
        return Text::with('moduleContent')->findOrFail($id);
    }

    public function updateText(Text $text, array $data): Text
    {
        return DB::transaction(function () use ($text, $data) {
            $text->update($data);

            if (isset($data['title'])) {
                $text->moduleContent()->update(['title' => $data['title']]);
            }

            return $text->load('moduleContent');
        });
    }

    public function deleteText(Text $text): void
    {
        DB::transaction(function () use ($text) {
            $text->moduleContent()->delete();
            $text->delete();
        });
    }
}
