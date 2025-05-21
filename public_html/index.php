<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EDBank - Educação Financeira Gamificada</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Custom Styles */
    .hero {
      background: linear-gradient(rgba(0,123,255,0.6), rgba(0,123,255,0.6)), url('https://source.unsplash.com/1600x900/?finance,game');
      background-size: cover;
      color: #fff;
      padding: 100px 0;
      text-align: center;
    }
    .feature-icon {
      font-size: 3rem;
      color: #007bff;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">EDBank</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="#">Início</a></li>
          <li class="nav-item"><a class="nav-link" href="#features">Recursos</a></li>
          <li class="nav-item"><a class="nav-link" href="#about">Sobre</a></li>
          <li class="nav-item"><a class="btn btn-primary" href="#signup">Entrar</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <h1 class="display-4">Bem-vindo ao EDBank</h1>
      <p class="lead">Aprenda juros, financiamentos e multas jogando!</p>
      <a href="#signup" class="btn btn-lg btn-light mt-3">Começar Agora</a>
    </div>
  </section>

  <!-- Features -->
  <section id="features" class="py-5">
    <div class="container">
      <div class="row text-center mb-4">
        <h2 class="col-12">Recursos Principais</h2>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-md">
            <div class="card-body text-center">
              <div class="feature-icon mb-3">💰</div>
              <h5 class="card-title">Simulação de Juros</h5>
              <p class="card-text">Calcule juros simples e compostos em tempo real.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
              <div class="feature-icon mb-3">📊</div>
              <h5 class="card-title">Dashboard Interativo</h5>
              <p class="card-text">Monitore seu saldo e histórico de transações.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body text-center">
              <div class="feature-icon mb-3">🏆</div>
              <h5 class="card-title">Ranking de Performance</h5>
              <p class="card-text">Compare sua pontuação com a turma.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About -->
  <section id="about" class="py-5 bg-light">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <h2>Sobre o EDBank</h2>
          <p>EDBank é um ambiente gamificado para ensinar conceitos de juros, financiamentos e multas de forma prática e divertida.</p>
        </div>
        <div class="col-md-6 text-center">
          <img src="https://source.unsplash.com/400x300/?education,finance" class="img-fluid rounded" alt="EDBank" />
        </div>
      </div>
    </div>
  </section>

  <!-- Signup CTA -->
  <section id="signup" class="py-5 text-center">
    <div class="container">
      <h2>Pronto para começar?</h2>
      <p>Crie sua conta gratuita e inicie a jornada financeira.</p>
      <a class="btn btn-primary btn-lg" href="#">Cadastre-se</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-white py-3">
    <div class="container text-center">
      <small>&copy; 2025 EDBank. Todos os direitos reservados.</small>
    </div>
  </footer>

  <!-- Bootstrap 5 JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

