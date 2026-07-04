<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>承認待ちユーザー詳細</title>
    @include('components.head')
</head>

<body class="is-subpage">
    <div class="l-container">

        @include('components.l-header')

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    @include('components.admin.nav')
                    <h1 class="p-sub__head01">承認待ちユーザー詳細</h1>
                    <div class="p-sub__inner">
                        <div class="c-user no-link">
                            <div class="c-user-icon">
                                <x-user-icon :user="$user" />
                            </div>
                            <div class="c-user-info">
                                <div class="c-user-name">{{ $user->name }}</div>
                                <div class="c-user-id">{{ $user->email }}</div>
                            </div>
                        </div>
                        <div class="c-admin-card">
                            <p class="c-admin-card__title">過去のIIMS活動参加歴</p>
                            @if(!empty($user->past_join))
                            <p class="c-admin-card__body">
                                @foreach($user->past_join as $val)
                                ・{{ $val }}<br>
                                @endforeach
                            </p>
                            @else
                            <p class="c-admin-card__body">選択されていません</p>
                            @endif
                        </div>
                        <form action="{{ route('admin.unapprovedUser.approve') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $user->id }}">
                            <div class="c-button-wrap">
                                <button type="submit" class="c-button">承認する</button>
                            </div>
                        </form>
                    </div>
                    @include('components.admin.footer')
                </section>
            </main>
            @include('components.l-footer-top')
        </div>
    </div>
    @include('components.javascript')
</body>

</html>