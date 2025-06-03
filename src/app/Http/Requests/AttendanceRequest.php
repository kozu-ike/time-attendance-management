<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'attendances' => ['required', 'array'],
            'attendances.*.work_date' => ['required', 'date'],
            'attendances.*.clock_in' => ['nullable', 'date_format:H:i'],
            'attendances.*.clock_out' => ['nullable', 'date_format:H:i'],
            'attendances.*.breaks' => ['nullable', 'array'],
            'attendances.*.breaks.*.break_in' => ['nullable', 'date_format:H:i'],
            'attendances.*.breaks.*.break_out' => ['nullable', 'date_format:H:i'],
            'attendances.*.remarks' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $attendances = $this->input('attendances', []);

            foreach ($attendances as $attendanceId => $data) {
                $clockIn = $data['clock_in'] ?? null;
                $clockOut = $data['clock_out'] ?? null;
                $breaks = $data['breaks'] ?? [];
                $remarks = $data['remarks'] ?? null;

                if ($clockIn && $clockOut) {
                    if (strtotime($clockIn) >= strtotime($clockOut)) {
                        $validator->errors()->add("attendances.{$attendanceId}.clock_in", "出勤時間もしくは退勤時間が不適切な値です");
                        $validator->errors()->add("attendances.{$attendanceId}.clock_out", "出勤時間もしくは退勤時間が不適切な値です");
                    }
                }

                foreach ($breaks as $idx => $break) {
                    $breakIn = $break['break_in'] ?? null;
                    $breakOut = $break['break_out'] ?? null;

                    if ($breakIn && $breakOut) {
                        if (($clockIn && strtotime($breakIn) < strtotime($clockIn)) ||
                            ($clockOut && strtotime($breakOut) > strtotime($clockOut))
                        ) {
                            $validator->errors()->add("attendances.{$attendanceId}.breaks.{$idx}.break_in", "休憩時間が勤務時間外です");
                            $validator->errors()->add("attendances.{$attendanceId}.breaks.{$idx}.break_out", "休憩時間が勤務時間外です");
                        }
                        if (strtotime($breakIn) >= strtotime($breakOut)) {
                            $validator->errors()->add("attendances.{$attendanceId}.breaks.{$idx}.break_in", "休憩時間もしくは終了時間が不適切な値です");
                            $validator->errors()->add("attendances.{$attendanceId}.breaks.{$idx}.break_out", "休憩時間もしくは終了時間が不適切な値です");
                        }
                    } elseif ($breakIn || $breakOut) {
                        $validator->errors()->add("attendances.{$attendanceId}.breaks.{$idx}", "休憩時間の開始・終了を正しく入力してください");
                    }
                }

                if (empty($remarks)) {
                    $validator->errors()->add("attendances.{$attendanceId}.remarks", "備考を記入してください");
                }
            }
        });
    }
}
