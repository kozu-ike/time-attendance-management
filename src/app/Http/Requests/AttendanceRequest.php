<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requested_clock_in' => ['required', 'date'],
            'requested_clock_out' => ['required', 'date'],
            'requested_breaks_json' => ['nullable', 'array'],
            'requested_breaks_json.*.break_in' => ['nullable', 'date'],
            'requested_breaks_json.*.break_out' => ['nullable', 'date'],
            'note' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'requested_clock_in.required' => '出勤時間を入力してください。',
            'requested_clock_out.required' => '退勤時間を入力してください。',
            'note.required' => '備考を記入してください。',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $clockIn = strtotime($this->input('requested_clock_in'));
            $clockOut = strtotime($this->input('requested_clock_out'));

            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add('requested_clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            $breaks = $this->input('requested_breaks_json', []);
            foreach ($breaks as $index => $break) {
                $breakIn = isset($break['break_in']) ? strtotime($break['break_in']) : null;
                $breakOut = isset($break['break_out']) ? strtotime($break['break_out']) : null;

                if ($breakIn && !$breakOut || !$breakIn && $breakOut) {
                    $validator->errors()->add("requested_breaks_json.$index", '休憩の開始と終了の両方を入力してください');
                }

                if ($breakIn && ($breakIn < $clockIn || $breakIn > $clockOut)) {
                    $validator->errors()->add("requested_breaks_json.$index.break_in", '休憩時間が勤務時間外です');
                }

                if ($breakOut && ($breakOut < $clockIn || $breakOut > $clockOut)) {
                    $validator->errors()->add("requested_breaks_json.$index.break_out", '休憩時間が勤務時間外です');
                }
            }

            if (empty(trim($this->input('note')))) {
                $validator->errors()->add('note', '備考を記入してください');
            }
        });
    }
}
