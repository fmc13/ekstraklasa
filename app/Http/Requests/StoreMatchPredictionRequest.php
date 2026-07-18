<?php

namespace App\Http\Requests;

use App\Enums\MatchPredictionResult;
use App\Models\Fixture;
use App\Services\MatchPredictionService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreMatchPredictionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var Fixture $fixture */
        $fixture = $this->route('fixture');

        return $this->user() !== null
            && app(MatchPredictionService::class)->canPredict($fixture);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'result' => ['required', new Enum(MatchPredictionResult::class)],
        ];
    }

    public function result(): MatchPredictionResult
    {
        return MatchPredictionResult::from($this->validated('result'));
    }
}
