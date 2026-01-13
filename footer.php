<?php
$bodyClass = view_get('bodyClass', '');
?>

<?php if ($bodyClass !== 'bg-login'): ?>
    <footer class="mynhi-footer main-footer">
        <div>Source Bếp Nhà <span class="heart-beat">💗</span> Myn</div>
        <div style="font-size: 0.8rem; opacity: 0.8; margin-top: 5px;">© 2026 DeeAyTee Kitchen Manager</div>
    </footer>
<?php endif; ?>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<script src="assets/js/main.js?v=1.0"></script>
<script src="assets/js/particles.js?v=1.0"></script>

<?php
$scripts = view_get('scripts', []);
foreach ($scripts as $src):
    ?>
    <script src="<?= e($src) ?>"></script>
<?php endforeach; ?>
</body>

</html>