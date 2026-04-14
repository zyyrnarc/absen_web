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
      --rose:    #9b2a3a;
      --rose-lt: #c0445a;
      --glass:   rgba(255,255,255,0.08);
      --border:  rgba(255,255,255,0.18);
      --text:    #f0e8e8;
      --muted:   rgba(240,232,232,0.55);
    }

    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Lato', sans-serif;
      background:
        linear-gradient(160deg, #1a0a0e 0%, #3b1020 40%, #5c1a2e 70%, #2a0810 100%);
      overflow: hidden;
    }

    /* mountain silhouette layer */
    body::before {
      content: '';
      position: fixed;
      inset: 0;
      background:
        radial-gradient(ellipse 120% 60% at 50% 110%,
          rgba(90,20,35,0.85) 0%, transparent 70%),
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 400'%3E%3Cpath fill='%23110408' fill-opacity='0.7' d='M0,400 L0,300 L120,200 L200,260 L300,150 L400,220 L500,100 L600,180 L720,80 L840,160 L920,60 L1020,140 L1100,40 L1200,130 L1300,200 L1440,160 L1440,400Z'/%3E%3C/svg%3E")
        center bottom / cover no-repeat;
      pointer-events: none;
      z-index: 0;
    }

    /* aurora glow */
    body::after {
      content: '';
      position: fixed;
      top: -30%; left: 10%;
      width: 80%; height: 60%;
      background: radial-gradient(ellipse, rgba(180,50,80,0.25) 0%, transparent 70%);
      filter: blur(60px);
      pointer-events: none;
      z-index: 0;
    }

    .card {
      position: relative;
      z-index: 1;
      width: min(92vw, 420px);
      padding: 2.8rem 2.4rem 2.4rem;
      background: var(--glass);
      border: 1px solid var(--border);
      border-radius: 20px;
      backdrop-filter: blur(22px) saturate(1.4);
      -webkit-backdrop-filter: blur(22px) saturate(1.4);
      box-shadow:
        0 8px 48px rgba(0,0,0,0.5),
        inset 0 1px 0 rgba(255,255,255,0.12);
      animation: fadeUp .6s ease both;
    }

    @keyframes fadeUp {
      from { opacity:0; transform:translateY(28px); }
      to   { opacity:1; transform:translateY(0); }
    }

    h1 {
      font-family: 'Cinzel', serif;
      font-size: 1.55rem;
      font-weight: 600;
      color: var(--text);
      text-align: center;
      letter-spacing: .12em;
      margin-bottom: 2rem;
    }

    .form-group { margin-bottom: 1.1rem; }

    .form-group input {
      width: 100%;
      padding: .82rem 1.1rem;
      background: rgba(255,255,255,0.07);
      border: 1px solid var(--border);
      border-radius: 50px;
      color: var(--text);
      font-family: 'Lato', sans-serif;
      font-size: .95rem;
      font-weight: 300;
      letter-spacing: .04em;
      outline: none;
      transition: border .25s, background .25s, box-shadow .25s;
    }

    .form-group input::placeholder { color: var(--muted); }

    .form-group input:focus {
      border-color: var(--rose-lt);
      background: rgba(255,255,255,0.12);
      box-shadow: 0 0 0 3px rgba(155,42,58,0.25);
    }

    .btn-login {
      width: 100%;
      padding: .85rem;
      margin-top: .6rem;
      background: linear-gradient(135deg, var(--rose), var(--rose-lt));
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
      box-shadow: 0 4px 20px rgba(155,42,58,0.45);
    }

    .btn-login:hover {
      filter: brightness(1.15);
      transform: translateY(-1px);
      box-shadow: 0 6px 28px rgba(155,42,58,0.6);
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
    .links a:hover { color: var(--text); }

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
      background: rgba(255,255,255,0.07);
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      transition: background .2s, transform .15s;
    }
    .social-btn:hover { background: rgba(255,255,255,0.14); transform: translateY(-2px); }
    .social-btn img { width: 22px; height: 22px; }

    /* toast */
    .toast {
      position: fixed;
      bottom: 2rem; left: 50%; transform: translateX(-50%) translateY(30px);
      padding: .7rem 1.4rem;
      border-radius: 8px;
      font-size: .85rem;
      font-weight: 700;
      letter-spacing: .06em;
      opacity: 0;
      transition: opacity .3s, transform .3s;
      pointer-events: none;
      z-index: 999;
    }
    .toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    .toast.success { background: #1e4d2b; color: #6ef07a; border: 1px solid #6ef07a44; }
    .toast.error   { background: #4d1e1e; color: #f07a7a; border: 1px solid #f07a7a44; }

    /* shake */
    @keyframes shake {
      0%,100%{transform:translateX(0)}
      20%{transform:translateX(-8px)}
      40%{transform:translateX(8px)}
      60%{transform:translateX(-5px)}
      80%{transform:translateX(5px)}
    }
    .shake { animation: shake .4s ease; }
  </style>
</head>
<body>

<div class="card">
  <h1>Welcome</h1>

  {{-- ERROR MESSAGE --}}
  @if(session('error'))
    <div style="color:red; text-align:center; margin-bottom:10px;">
      {{ session('error') }}
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