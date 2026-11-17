</div><!-- /content -->
</div><!-- /card -->

<footer class="text-center text-secondary small py-4">
    <div>Blogify &amp; Post Management System &copy; <?= date("Y") ?></div>
</footer>
</div><!-- /container -->
</div>

<!-- Custom In-App Delete Confirmation Modal (Replaces native browser alert) -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 bg-danger bg-opacity-10 pb-2">
                <div class="d-flex align-items-center gap-2 text-danger">
                    <div class="rounded-circle bg-danger bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    </div>
                    <h5 class="modal-title fw-bold mb-0" id="deleteConfirmModalLabel">Delete Post</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-2 fs-6">Are you sure you want to delete <strong id="deletePostTitle" class="text-body-emphasis">this post</strong>?</p>
                <div class="text-secondary small">
                    This action cannot be undone and will permanently remove this blog post and its associated image.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <!-- Delete is sent as POST with a CSRF token (see delete.php) -->
                <form method="post" action="delete.php" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="deletePostId" value="">
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Delete Confirmation Modal Script -->
<script>
    (function () {
        const deleteModal = document.getElementById('deleteConfirmModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const triggerBtn = event.relatedTarget;
                if (!triggerBtn) return;
                const postId = triggerBtn.getAttribute('data-post-id') || '';
                const postTitle = triggerBtn.getAttribute('data-post-title') || 'this post';

                const titleElem = document.getElementById('deletePostTitle');
                const idInput = document.getElementById('deletePostId');

                if (titleElem) {
                    titleElem.textContent = `"${postTitle}"`;
                }
                if (idInput) {
                    idInput.value = postId;
                }
            });
        }
    })();
</script>

<!-- Theme Switcher Logic (Bootstrap 5 color mode & Synchronized Custom Switches) -->
<script>
    (function () {
        const themeSwitches = document.querySelectorAll('.themeSwitchInput, #themeSwitch');
        const themeToggleBtn = document.getElementById('themeToggle');
        const icon = document.getElementById('themeIcon');

        function syncThemeUI(theme) {
            themeSwitches.forEach(sw => {
                sw.checked = (theme === 'dark');
            });
            if (icon) {
                icon.className = theme === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
            }
        }

        const currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        syncThemeUI(currentTheme);

        themeSwitches.forEach(sw => {
            sw.addEventListener('change', function () {
                const nextTheme = this.checked ? 'dark' : 'light';
                document.documentElement.setAttribute('data-bs-theme', nextTheme);
                localStorage.setItem('blog_theme', nextTheme);
                syncThemeUI(nextTheme);
            });
        });

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function () {
                const activeTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
                const nextTheme = activeTheme === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-bs-theme', nextTheme);
                localStorage.setItem('blog_theme', nextTheme);
                syncThemeUI(nextTheme);
            });
        }
    })();
</script>
</body>

</html>