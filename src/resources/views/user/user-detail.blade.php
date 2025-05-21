@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/user-detail.css') }}">
@endsection

@section('content')
    @if($errors->any())
        <ul class="text-red-600">
            @foreach($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    @endif
    <div class="detail__content">
        <div class="detail__header">
            <h2 class="content__header--item">勤怠詳細</h2>
        </div>
        <form class="form" action="{{ url('/attendance/' . $data['id']) }}" method="post">
            @csrf

            @if (is_null($data['application']))
                {{-- 承認待ちが無い場合：修正フォーム --}}
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
                            {{-- 年部分は固定表示のみ --}}
                            <input class="form__input" type="text" value="{{ $data['year'] }}" readonly>
                            {{-- 修正用日付 --}}
                            <input class="form__input" type="text" name="new_date" value="{{ $data['date'] }}">
                        </div>
                    </div>

                    <div class="form__group">
                        <p class="form__header">出勤・退勤</p>
                        <div class="form__input-group">
                            <input class="form__input" type="text" name="new_clock_in" value="{{ $data['clock_in'] }}">
                            <p>〜</p>
                            <input class="form__input" type="text" name="new_clock_out" value="{{ $data['clock_out'] }}">
                        </div>
                    </div>

                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error('new_clock_in') {{ $message }} @enderror
                            @error('new_clock_out') {{ $message }} @enderror
                        </div>
                    </div>

                    <div class="form__group form__break-group">
                        <p class="form__header">休憩</p>
                        <div class="form__input-wrapper">
                            @foreach($data['breaks'] as $break)
                                <div class="form__input-group">
                                    <input class="form__input" type="text" name="new_break_in[]" value="{{ $break['break_in'] }}">
                                    <p>〜</p>
                                    <input class="form__input" type="text" name="new_break_out[]" value="{{ $break['break_out'] }}">
                                </div>
                            @endforeach
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
                            @error('new_break_in[]') {{ $message }} @enderror
                            @error('new_break_out[]') {{ $message }} @enderror
                        </div>
                    </div>

                    <div class="form__group">
                        <p class="form__header">備考</p>
                        <div class="form__input-group">
                            <input class="form__textarea" name="comment" value="{{ $data['comment'] }}">
                        </div>
                    </div>

                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error('comment') {{ $message }} @enderror
                        </div>
                    </div>
                </div>

                <div class="form__button">
                    <button class="form__button--submit" type="submit">修正</button>
                </div>

            @else
                {{-- 承認待ちあり：閲覧のみ --}}
                <div class="form__content">
                    <div class="form__group">
                        <p class="form__header">名前</p>
                        <div class="form__input-group">
                            <input class="form__input form__input--name readonly" type="text" value="{{ $user->name }}"
                                readonly>
                        </div>
                    </div>

                    <div class="form__group">
                        <p class="form__header">日付</p>
                        <div class="form__input-group">
                            <input class="form__input readonly" type="text" value="{{ $data['year'] }}" readonly>
                            <input class="form__input readonly" type="text" value="{{ $data['date'] }}" readonly>
                        </div>
                    </div>

                    <div class="form__group">
                        <p class="form__header">出勤・退勤</p>
                        <div class="form__input-group">
                            <input class="form__input readonly" type="text" value="{{ $data['clock_in'] }}" readonly>
                            <p>〜</p>
                            <input class="form__input readonly" type="text" value="{{ $data['clock_out'] }}" readonly>
                        </div>
                    </div>

                    <div class="form__group form__break-group">
                        <p class="form__header">休憩</p>
                        <div class="form__input-wrapper">
                            @foreach($data['breaks'] as $break)
                                <div class="form__input-group">
                                    <input class="form__input readonly" type="text" name="new_break_in[]"
                                        value="{{ $break['break_in'] }}" readonly>
                                    <p>〜</p>
                                    <input class="form__input readonly" type="text" name="new_break_out[]"
                                        value="{{ $break['break_out'] }}" readonly>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="form__group">
                        <p class="form__header">備考</p>
                        <div class="form__input-group">
                            <input class="form__textarea readonly" name="comment" value="{{ $data['comment'] }}"
                                readonly></input>
                        </div>
                    </div>
                </div>

                <div class="form__button">
                    <p class="readonly-message">承認待ちのため修正できません</p>
                </div>
            @endif

        </form>
    </div>
@endsection
