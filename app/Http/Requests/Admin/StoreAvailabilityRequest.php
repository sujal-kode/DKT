<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->date && $this->start_time) {
                $startDateTime = \Illuminate\Support\Carbon::parse($this->date.' '.$this->start_time);
                if ($startDateTime->isPast()) {
                    $validator->errors()->add('start_time', 'The start time must be in the future.');
                    return;
                }
            }

            $doctor = $this->route('doctor');
            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : (int) $doctor;

            if (\App\Models\Availability::overlapsExisting($doctorId, $this->date, $this->start_time, $this->end_time)) {
                $validator->errors()->add('start_time', 'This availability period overlaps with an existing availability period.');
            }
        });
    }
}
