@if($errors->any())
<div class="form-error">
  <ul>
    @foreach($errors->all() as $error)
    <li class="error-text">・{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif
