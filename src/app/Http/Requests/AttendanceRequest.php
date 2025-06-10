<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Auth;


class AttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() || Auth::guard('admin')->check();
    }

    public function rules(): array
    {
        return [
            'attendances' => ['required', 'array'],
            'attendances.*.clock_in' => ['nullable'],
            'attendances.*.clock_out' => ['nullable'],
            'attendances.*.breaks' => ['nullable', 'array'],
            'attendances.*.breaks.*.break_in' => ['nullable'],
            'attendances.*.breaks.*.break_out' => ['nullable'],
            'attendances.*.remarks' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $attendances = $this->input('attendances', []);

            foreach ($attendances as $attendanceId => $data) {
                $clockIn = $data['clock_in'] ?? null;
                $clockOut = $data['clock_out'] ?? null;
                $breaks = $data['breaks'] ?? [];
                $remarks = $data['remarks'] ?? null;

                // 出退勤の不整合
                if ($clockIn && $clockOut) {
                    if (strtotime($clockIn) >= strtotime($clockOut)) {
                        $validator->errors()->add("attendances.{$attendanceId}.clock_in", "出勤時間もしくは退勤時間が不適切な値です");
                        $validator->errors()->add("attendances.{$attendanceId}.clock_out", "出勤時間もしくは退勤時間が不適切な値です");
                    }
                }

                // 休憩時間チェック
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
                            // このチェックは除外してよければコメントアウトしてください
                            $validator->errors()->add("attendances.{$attendanceId}.breaks.{$idx}.break_in", "休憩時間が勤務時間外です");
                            $validator->errors()->add("attendances.{$attendanceId}.breaks.{$idx}.break_out", "休憩時間が勤務時間外です");
                        }
                    }
                }

                // 備考が空
                if (empty($remarks)) {
                    $validator->errors()->add("attendances.{$attendanceId}.remarks", "備考を記入してください");
                }
            }
        });
    }
}
