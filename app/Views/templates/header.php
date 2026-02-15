<!DOCTYPE html>
<html lang="it">

<head>
    <title>MVC</title>
    <meta charset="utf-8">
    <meta name="author" content="f.n.">
    <meta name="description" content="mvc">
    <title>Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('styles/style.css') ?>">
</head>




<body>


    <nav class="navbar navbar-expand-lg navbar-dark p-3 bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.html">
                <i class="fas fa-chart-line"></i> FinEdu
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="Market.html">Analisi Mercati</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="Education.html">Educazione Finanziaria</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="portfolioDrop" role="button"
                            data-bs-toggle="dropdown">
                            Portafoglio
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="Portfolio.html">I miei Portafogli</a></li>
                            <li><a class="dropdown-item" href="Orders.html">Storico Ordini</a></li>
                        </ul>
                    </li>
                </ul>
                <?php
                if (session()->has('logged')):
                    ?>
                    <div class="d-flex">
                        <div class="dropdown">
                            <button class="btn btn-outline-light dropdown-toggle me-2" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i>
                                <?= session()->get('first_name') . " " . session()->get('last_name') ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/UserController/profile">Profilo</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="/AuthController/logout">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                    <?php
                else:
                    ?>
                    <div class="d-flex">
                        <button class="btn btn-light text-primary me-2" data-bs-toggle="modal"
                            data-bs-target="#loginModal">Accedi</button>
                        <button class="btn btn-outline-light" data-bs-toggle="modal"
                            data-bs-target="#registerModal">Registrati</button>
                    </div>
                    <?php
                endif;
                ?>
            </div>
        </div>
    </nav>
    <?= $this->include('templates/modals') ?>
    <?php if (session()->role == "admin"):
        echo view("templates/adminSidebar");
    endif; ?>


    <?php if (isset($login_error) && $login_error != "" || isset($signup_success)): ?>
        <script>
            //after the DOM is ready modal opens
            document.addEventListener("DOMContentLoaded", function () {
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                loginModal.show();
            });
        </script>
        <?php
    elseif (isset($signup_error) && $signup_error != ""): ?>
        <script>

            document.addEventListener("DOMContentLoaded", function () {
                var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
                registerModal.show();
            });

        </script>
        <?php
    endif; ?>
    <!-- remove error after modal gets closed -->
    <script>
        document.getElementById('loginModal').addEventListener('hidden.bs.modal', function () {
            let error = this.querySelector('.text-danger');
            if (error) error.innerHTML = '';
        });

        document.getElementById('registerModal').addEventListener('hidden.bs.modal', function () {
            let error = this.querySelector('.text-danger');
            if (error) error.innerHTML = '';
        });
    </script>