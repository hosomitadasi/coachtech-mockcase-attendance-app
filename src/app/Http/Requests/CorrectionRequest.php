<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
{
    return [
        'new_clock_in'   => 'required|date_format:H:i',
        'new_clock_out'  => 'required|date_format:H:i|after:new_clock_in',

        // ★ 配列で受け取る宣言
        'new_break_in'   => 'nullable|array',
        'new_break_out'  => 'nullable|array',

        // ★ 配列中の各要素をチェック
        'new_break_in.*'  => 'nullable|date_format:H:i|after:new_clock_in|before:new_break_out.*',
        'new_break_out.*' => 'nullable|date_format:H:i|after:new_break_in.*|before:new_clock_out',

        'comment'        => 'required',
    ];
}


    public function messages()
    {
        return [
            'new_clock_in.required' => '出勤時間を入力してください。',
            'new_clock_out.required' => '退勤時間を入力してください。',
            'new_clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'new_break_in.*.date_format'  => '休憩開始は「HH:mm」形式で入力してください。',
            'new_break_in.*.after'        => '休憩開始は出勤時間以降の時刻を選択してください。',
            'new_break_in.*.before'       => '休憩開始は休憩終了より前の時刻を選択してください。',
            'new_break_out.*.date_format' => '休憩終了は「HH:mm」形式で入力してください。',
            'new_break_out.*.after'       => '休憩終了は休憩開始以降の時刻を選択してください。',
            'new_break_out.*.before'      => '休憩終了は退勤時間より前の時刻を選択してください。',
            'comment.required' => '備考を記入してください。'
        ];
    }
}
