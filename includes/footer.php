            </div><!-- /app-content -->
        </div><!-- /app-card -->

        <footer class="app-footer text-center mt-4">
            <div>Blogify &amp; Post Management System &copy; <?= date("Y") ?></div>
        </footer>
    </div><!-- /container -->
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Theme Switcher Logic -->
<script>
    (function() {
        const toggleBtn = document.getElementById('themeToggle');
        if (!toggleBtn) return;

        function updateAria(theme) {
            toggleBtn.setAttribute('aria-checked', theme === 'dark' ? 'true' : 'false');
        }

        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        updateAria(currentTheme);

        toggleBtn.addEventListener('click', function() {
            const activeTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
            const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-bs-theme', nextTheme);
            localStorage.setItem('blog_theme', nextTheme);
            updateAria(nextTheme);
        });
    })();
</script>
</body>
</html>
