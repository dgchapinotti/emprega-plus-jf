<footer class="footer mt-auto">
    <div class="container">
        <p class="mb-1 fw-semibold">Emprega+ Juiz de Fora</p>
        <p class="mb-0 small">
            &copy; <?= date('Y') ?> — Todos os direitos reservados.
        </p>
        <p class="mb-0 mt-2 small">
            <a class="text-white" href="<?= url('institucional/privacidade.php') ?>">Política de Privacidade</a>
            <span class="mx-2" aria-hidden="true">|</span>
            <a class="text-white" href="<?= url('institucional/termos.php') ?>">Termos de Uso</a>
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<?php $versaoJs = (string) (@filemtime(__DIR__ . '/../assets/js/app.js') ?: time()); ?>
<script src="<?= htmlspecialchars(url('assets/js/app.js') . '?v=' . $versaoJs, ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
