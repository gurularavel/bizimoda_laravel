<!DOCTYPE html>
<html lang="az">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin giriş — {{ setting('site.name', 'bizimoda') }}</title>
  <link rel="icon" href="{{ asset('images/favicon.png') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #1f2533; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .login-card { width: 100%; max-width: 380px; }
    .btn-brand { background: #ed3237; color: #fff; }
    .btn-brand:hover { background: #ca1f1f; color: #fff; }
  </style>
</head>
<body>
  <div class="login-card px-3">
    <div class="text-center mb-4"><img src="{{ asset('images/logo.png') }}" alt="bizimoda" style="height:36px;filter:brightness(0) invert(1)"></div>
    <div class="card shadow border-0">
      <div class="card-body p-4">
        <h1 class="h5 mb-3">Admin panelə giriş</h1>
        <form method="post" action="{{ route('admin.login') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label" for="email">E-poçt</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required autofocus>
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label class="form-label" for="password">Şifrə</label>
            <input type="password" id="password" name="password" class="form-control" required>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember">
            <label class="form-check-label" for="remember">Yadda saxla</label>
          </div>
          <button type="submit" class="btn btn-brand w-100">Daxil ol</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
