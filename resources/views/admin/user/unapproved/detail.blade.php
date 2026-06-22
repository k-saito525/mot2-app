<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>承認待ちユーザー詳細</title>
    @include('components.head')
</head>

<body class="is-subpage">
    <div class="l-container">

        <!-- l-header START -->
        <!-- l-header END -->

        <div class="l-contents">
            <main class="l-main">
                <section class="p-sub__section">
                    <h1 class="p-sub__head01">承認待ちユーザー詳細</h1>
                    <div class="p-sub__inner">
                        <div class="c-user">
                            <div class="c-user-icon">
                                <img src="{{ ('/img/common/dummy_icon.png') }}" alt="">
                            </div>
                            <div class="c-user-info">
                                <div class="c-user-name">【氏名】{{ $user->name }}</div>
                                <div class="c-user-email">【メールアドレス】{{ $user->email }}</div>
                                <div class="c-user-past-join">【過去のIIMS活動参加歴】</div>
                                @if(!empty($user->past_join))
                                @foreach($user->past_join as $key => $val)
                                <p>・{{ $val }}</p>
                                @endforeach
                                @else
                                <p>選択されていません</p>
                                @endif
                                <form action="{{ route('admin.unapprovedUser.approve') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $user->id }}">
                                    <button type="submit">承認する</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
            <!-- l-footer START -->
            @include('components.l-footer-top')
            <!-- l-footer END -->
        </div>
    </div>
    @include('components.javascript')
</body>

</html>