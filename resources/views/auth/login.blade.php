<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy-1:  #0f1727;
      --navy-2:  #172033;
      --blue:    #335cff;
      --blue-2:  #4c8fff;
      --sky:     #0ea5e9;
      --glass:   rgba(255,255,255,0.10);
      --glass-2: rgba(255,255,255,0.16);
      --border:  rgba(191, 214, 255, 0.24);
      --text:    #eef5ff;
      --muted:   rgba(225, 235, 255, 0.68);
      --danger:  #fecaca;
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Lato', sans-serif;
      background:
        radial-gradient(circle at top left, rgba(76, 143, 255, 0.18), transparent 28%),
        radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.16), transparent 24%),
        linear-gradient(155deg, var(--navy-1) 0%, var(--navy-2) 42%, #12284d 100%);
      overflow: hidden;
    }

    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 120% 60% at 50% 110%,
          rgba(27, 53, 94, 0.92) 0%, transparent 72%),
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 400'%3E%3Cpath fill='%23110408' fill-opacity='0.7' d='M0,400 L0,300 L120,200 L200,260 L300,150 L400,220 L500,100 L600,180 L720,80 L840,160 L920,60 L1020,140 L1100,40 L1200,130 L1300,200 L1440,160 L1440,400Z'/%3E%3C/svg%3E")
        center bottom / cover no-repeat;
      opacity: .35;
      pointer-events: none;
      z-index: 0;
    }

    body::after {
      content: '';
      position: fixed;
      top: -22%; left: 8%;
      width: 84%; height: 62%;
      background: radial-gradient(ellipse, rgba(76, 143, 255, 0.22) 0%, transparent 68%);
      filter: blur(76px);
      pointer-events: none;
      z-index: 0;
    }

    .card {
      position: relative;
      z-index: 1;
      width: min(92vw, 430px);
      padding: 2.8rem 2.4rem 2.3rem;
      background: var(--glass);
      border: 1px solid var(--border);
      border-radius: 24px;
      backdrop-filter: blur(24px) saturate(1.4);
      -webkit-backdrop-filter: blur(24px) saturate(1.4);
      box-shadow:
        0 24px 64px rgba(2, 8, 23, 0.42),
        inset 0 1px 0 rgba(255,255,255,0.14);
      animation: fadeUp .6s ease both;
    }

    @keyframes fadeUp {
      from { opacity:0; transform:translateY(28px); }
      to   { opacity:1; transform:translateY(0); }
    }

    h1 {
      font-family: 'Cinzel', serif;
      font-size: 1.6rem;
      font-weight: 600;
      color: var(--text);
      text-align: center;
      letter-spacing: .12em;
      margin-bottom: .5rem;
    }

    .subtitle {
      text-align: center;
      color: var(--muted);
      font-size: .92rem;
      margin-bottom: 1.8rem;
    }

    .form-group { margin-bottom: 1.1rem; }

    .form-group input {
      width: 100%;
      padding: .9rem 1.1rem;
      background: rgba(255,255,255,0.08);
      border: 1px solid rgba(191, 214, 255, 0.18);
      border-radius: 50px;
      color: var(--text);
      font-family: 'Lato', sans-serif;
      font-size: .95rem;
      font-weight: 400;
      letter-spacing: .02em;
      outline: none;
      transition: border .25s, background .25s, box-shadow .25s;
    }

    .form-group input::placeholder { color: var(--muted); }

    .form-group input:focus {
      border-color: var(--blue-2);
      background: rgba(255,255,255,0.13);
      box-shadow: 0 0 0 4px rgba(76, 143, 255, 0.18);
    }

    .btn-login {
      width: 100%;
      padding: .85rem;
      margin-top: .6rem;
      background: linear-gradient(135deg, var(--blue), var(--sky));
      border: none;
      border-radius: 50px;
      color: #fff;
      font-family: 'Lato', sans-serif;
      font-size: .9rem;
      font-weight: 700;
      letter-spacing: .18em;
      text-transform: uppercase;
      cursor: pointer;
      transition: filter .25s, transform .15s, box-shadow .25s;
      box-shadow: 0 12px 28px rgba(51, 92, 255, 0.32);
    }

    .btn-login:hover {
      filter: brightness(1.08);
      transform: translateY(-1px);
      box-shadow: 0 16px 34px rgba(51, 92, 255, 0.38);
    }

    .btn-login:active { transform: translateY(0); filter: brightness(.95); }

    .btn-login:disabled {
      opacity: .6; cursor: not-allowed; transform: none;
    }

    .links {
      display: flex;
      justify-content: space-between;
      margin-top: .95rem;
    }

    .links a {
      color: var(--muted);
      font-size: .8rem;
      text-decoration: none;
      transition: color .2s;
    }
    .links a:hover { color: #ffffff; }

    .divider {
      display: flex;
      align-items: center;
      gap: .8rem;
      margin: 1.4rem 0 1.1rem;
      color: var(--muted);
      font-size: .72rem;
      letter-spacing: .12em;
      text-transform: uppercase;
    }
    .divider::before, .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border);
    }

    .social-row {
      display: flex;
      justify-content: center;
      gap: 1rem;
    }

    .social-btn {
      width: 46px; height: 46px;
      border-radius: 50%;
      border: 1px solid var(--border);
      background: rgba(255,255,255,0.08);
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      transition: background .2s, transform .15s;
    }
    .social-btn:hover { background: rgba(255,255,255,0.16); transform: translateY(-2px); }
    .social-btn img { width: 22px; height: 22px; }

    .error-box {
      color: var(--danger);
      text-align: center;
      margin-bottom: 14px;
      font-size: .9rem;
      font-weight: 700;
      padding: .8rem 1rem;
      border-radius: 14px;
      background: rgba(127, 29, 29, 0.28);
      border: 1px solid rgba(254, 202, 202, 0.18);
    }
  </style>
</head>
<body>

<div class="card">
  <h1>Welcome</h1>
  <p class="subtitle">Masuk ke panel admin dengan tampilan yang lebih tenang dan jelas.</p>

  @if ($errors->any())
    <div class="error-box">
      {{ $errors->first() }}
    </div>
  @endif

  <form method="POST" action="{{ route('login.process') }}">
    @csrf

    <div class="form-group">
      <input type="email" name="email" placeholder="Email" required />
    </div>

    <div class="form-group">
      <input type="password" name="password" placeholder="Password" required />
    </div>

    <button class="btn-login" type="submit">
      LOGIN
    </button>
  </form>

  <div class="links">
    <!-- HAPUS # -->
    <a href="javascript:void(0)">Forgot Password ?</a>
  </div>

  <div class="divider">OR LOGIN WITH</div>

  <div class="social-row">
    <button class="social-btn" type="button">
      <!-- icon tetap -->
      <svg width="22" height="22" viewBox="0 0 48 48">
        <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9 3.2l6.7-6.7C35.8 2.5 30.2 0 24 0 14.6 0 6.6 5.4 2.6 13.3l7.8 6C12.3 13 17.7 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.4-4 6.9-9.9 7.1-17z"/>
        <path fill="#FBBC05" d="M10.4 28.7A14.4 14.4 0 0 1 9.5 24c0-1.6.3-3.2.8-4.7l-7.8-6A23.8 23.8 0 0 0 0 24c0 3.9.9 7.5 2.6 10.7l7.8-6z"/>
        <path fill="#34A853" d="M24 48c6.2 0 11.4-2 15.2-5.5l-7.5-5.8c-2 1.4-4.6 2.2-7.7 2.2-6.3 0-11.7-4.2-13.6-9.9l-7.8 6C6.6 42.6 14.6 48 24 48z"/>
      </svg>
    </button>
  </div>
</div>

</body>
</html>
