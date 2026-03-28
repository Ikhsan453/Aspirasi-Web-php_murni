</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/main.js') ?>"></script>
<script>
function toggleAdminSidebar() {
    document.getElementById('adminSidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(el) {
            var a = bootstrap.Alert.getOrCreateInstance(el);
            if (a) a.close();
        });
    }, 5000);
});
</script>
<?= $extraScripts ?? '' ?>
</body>
</html>
