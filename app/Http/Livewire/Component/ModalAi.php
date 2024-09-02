<?php

namespace App\Http\Livewire\Component;

use Livewire\Component;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\HarmBlockThreshold;
use Gemini\Data\SafetySetting;
use Gemini\Enums\HarmCategory;
use Illuminate\Support\Str;

class ModalAi extends Component
{
    public $result;
    public $promp;

    public function gemini()
    {
        $this->result = '';
        $safetySettingDangerousContent = new SafetySetting(
            category: HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT,
            threshold: HarmBlockThreshold::BLOCK_ONLY_HIGH
        );

        $safetySettingHateSpeech = new SafetySetting(
            category: HarmCategory::HARM_CATEGORY_HATE_SPEECH,
            threshold: HarmBlockThreshold::BLOCK_ONLY_HIGH
        );

        $generationConfig = new GenerationConfig(
            stopSequences: [
                'Title',
            ],
            maxOutputTokens: 800,
            temperature: 1,
            topP: 0.8,
            topK: 10
        );

        try {
            $result = Gemini::geminiPro()
                ->withSafetySetting($safetySettingDangerousContent)
                ->withSafetySetting($safetySettingHateSpeech)
                ->withGenerationConfig($generationConfig)
                ->generateContent($this->promp);
            $this->result = Str::markdown($result->text());
        } catch (\Exception $response) {
            $this->result = $response->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.component.modal-ai');
    }
}
