<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Paws Store</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <style>
    :root {
      --cream: #faf6f0;
      --brown: #5c4033;
      --accent: #c9a227;
      --accent-soft: #e8d5a3;
      --text: #2d2a26;
      --text-muted: #6b6560;
      --white: #ffffff;
      --shadow: 0 4px 20px rgba(92, 64, 51, 0.08);
      --radius: 16px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Outfit', sans-serif;
      background: var(--cream);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
    }

    .register-wrap {
      max-width: 440px;
      width: 100%;
      background: var(--white);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 2.5rem;
    }

    h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.5rem;
      color: var(--brown);
      margin-bottom: 0.5rem;
    }

    .sub {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 1.5rem;
    }

    .form-group {
      margin-bottom: 1rem;
    }

    .form-group label {
      display: block;
      font-weight: 500;
      font-size: 0.9rem;
      margin-bottom: 0.4rem;
    }

    .form-group input {
      width: 100%;
      padding: 0.85rem 1rem;
      border: 1px solid rgba(92, 64, 51, 0.2);
      border-radius: 10px;
      font-size: 1rem;
      font-family: inherit;
    }

    .form-group input:focus {
      outline: none;
      border-color: var(--accent);
    }

    .btn {
      width: 100%;
      padding: 0.9rem;
      background: var(--accent);
      color: var(--white);
      border: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      font-family: inherit;
      margin-top: 0.5rem;
    }

    .login-link {
      text-align: center;
      margin-top: 1.5rem;
      font-size: 0.95rem;
      color: var(--text-muted);
    }

    .login-link a {
      color: var(--accent);
      font-weight: 500;
      text-decoration: none;
    }
  </style>
</head>

<body>
  <div class="register-wrap">
    <h1>Create account</h1>
    <form>
      <div class="form-group">
        <label>username</label>
        <input type="text" placeholder="Your name">
      </div>
      <div class="form-group">
        <label>Email address</label>
        <input type="email" placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Phone number</label>
        <input type="tel" placeholder="+91 98765 43210">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" placeholder="Min 8 characters">
      </div>
      <button type="submit" class="btn">Register</button>
    </form>
    <p class="login-link">Already have an account? <a href="login.php">Login</a></p>
  </div>
</body>

</html>