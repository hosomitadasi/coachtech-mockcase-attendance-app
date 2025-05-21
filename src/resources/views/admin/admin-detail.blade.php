@extends('layouts.admin-app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/user-detail.css') }}">
@endsection

@section('content')
    <div class="detail__content">
        <div class="detail__header">
            <h2 class="content__header--item">勤怠詳細</h2>
        </div>
        <form class="form" action="{{ url('/attendance/' . $attendanceRecord['id']) }}" method="post">
            @csrf
                <div class="form__content">
                    <div class="form__group">
                        <p class="form__header">名前</p>
                        <div class="form__input-group">
                            <input class="form__input form__input--name" type="text" name="name" value="{{ $user->name }}"
                                readonly>
                        </div>
                    </div>
                    <div class="form__group">
                        <p class="form__header">日付</p>
                        <div class="form__input-group">
                            <input class="form__input" type="text" name="new_date" value="{{ $attendanceRecord['year'] }}">
                            <input class="form__input" type="text" name="new_date" value="{{ $attendanceRecord['date'] }}">
                        </div>
                    </div>

                    <div class="form__group">
                        <p class="form__header">出勤・退勤</p>
                        <div class="form__input-group">
                            <input class="form__input" type="text" name="new_clock_in"
                                value="{{ $attendanceRecord['clock_in'] }}">
                            <p>〜</p>
                            <input class="form__input" type="text" name="new_clock_out"
                                value="{{ $attendanceRecord['clock_out'] }}">
                        </div>
                    </div>

                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error('new_clock_in')
                                {{ $message }}
                            @enderror
                            @error('new_clock_out')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <div class="form__group form__break-group">
                        <p class="form__header">休憩</p>
                        <div class="form__input-wrapper">
                            @if (isset($attendanceRecord['breaks']) && is_array($attendanceRecord['breaks']) && count($attendanceRecord['breaks']) > 0)
                                @foreach($attendanceRecord['breaks'] as $break)
                                    <div class="form__input-group">
                                        <input class="form__input" type="text" name="new_break_in[]"
                                            value="{{ $break['break_in'] ?? '' }}">
                                        <p>〜</p>
                                        <input class="form__input" type="text" name="new_break_out[]"
                                            value="{{ $break['break_out'] ?? '' }}">
                                    </div>
                                @endforeach
                            @endif

                            {{-- 空の入力行を1つ追加（ユーザーが追加できるように） --}}
                            <div class="form__input-group">
                                <input class="form__input" type="text" name="new_break_in[]" value="">
                                <p>〜</p>
                                <input class="form__input" type="text" name="new_break_out[]" value="">
                            </div>
                        </div>
                    </div>


                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @if($errors->has('new_break_in'))
                                @foreach ($errors->get('new_break_in') as $message)
                                    <p>{{ $message }}</p>
                                @endforeach
                            @endif

                            @if($errors->has('new_break_out'))
                                @foreach ($errors->get('new_break_out') as $message)
                                    <p>{{ $message }}</p>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="form__group">
                        <p class="form__header">備考</p>
                        <div class="form__input-group">
                            <textarea class="form__textarea" name="comment" id="">{{ $attendanceRecord['comment'] }}</textarea>
                        </div>
                    </div>
                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error ('comment')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form__button">
                    <button class="form__button--submit" type="submit">修正</button>
                </div>
        </form>
    </div>
@endsection
