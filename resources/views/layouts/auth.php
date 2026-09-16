<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Helpers\e($title ?? 'Autenticação - Asoftmedia') ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/custom.css" rel="stylesheet">
    <style>
        body.auth-bg {
            background: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.85)), url('<?= \App\Helpers\asset('images/login-bg.png') ?>') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }
        .auth-card {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.96);
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.4) !important;
        }
    </style>
</head>
<body class="auth-bg d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <?= $content ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
