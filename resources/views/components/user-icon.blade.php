@props(['user'])
@if(!empty($user?->user_icon))
<img src="{{ asset('storage/' . $user->user_icon) }}" alt="">
@else
<img src="/img/common/dummy_icon.png" alt="">
@endif
