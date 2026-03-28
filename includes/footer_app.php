</main>
<footer class="mt-5 py-4" style="background:var(--primary-dark);border-top:1px solid var(--secondary-blue);">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5 class="text-light-blue mb-3"><i class="fas fa-school me-2"></i>Sistem Aspirasi Web</h5>
                <p class="text-muted-custom mb-0">Platform digital untuk melaporkan dan memantau kondisi sarana dan prasarana sekolah.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted-custom mb-0">&copy; <?= date('Y') ?> Sistem Aspirasi Web. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/main.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(el) {
            bootstrap.Alert.getOrCreateInstance(el)?.close();
        });
    }, 5000);
});
</script>
<?= $extraScripts ?? '' ?>
</body>
</html>
