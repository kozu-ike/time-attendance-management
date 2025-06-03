@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('メール認証') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                    <div class="alert alert-success" role="alert">
                        {{ __('認証メールを再送信しました。') }}
                    </div>
                    @endif

                    {{ __('登録していただいたメールアドレスに認証メールを送付しました。') }}
                    <br>
                    {{ __('メール認証を完了してください。') }}

                    <br><br>
                    <form method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">
                            {{ __('認証メールを再送する') }}
                        </button>
                    </form>
                    <div class="login__link">
                        <a class="login__button-submit" href="/login">ログインはこちら</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection