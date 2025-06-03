<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証のお知らせ</title>
    <link rel="stylesheet" href="{{ asset('css/verify.css') }}">
</head>

<body>
    <h1>メール認証のお知らせ</h1>
    <p>{{ $name }} 様</p>
    <p>下のリンクをクリックして、メール認証を完了してください。</p>
    <a href="{{ $verificationUrl }}">認証リンク</a>
</body>

</html>