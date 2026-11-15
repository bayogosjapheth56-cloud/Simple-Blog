<?php
// create.php - INSERT (add new post with photo)
require_once "db_connect.php";
$pageTitle = "New Post";
$errors = [];
$title = $author = $category = $content = "";
$status = "Draft";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title    = trim($_POST["title"] ?? "");
    $author   = trim($_POST["author"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $content  = trim($_POST["content"] ?? "");
    $status   = $_POST["status"] ?? "Draft";

    if ($title === "")    $errors[] = "Title is required.";
    if ($author === "")   $errors[] = "Author is required.";
    if ($category === "") $errors[] = "Category is required.";
    if ($content === "")  $errors[] = "Content is required.";
    if (!in_array($status, ["Draft", "Published"])) $status = "Draft";

    // Handle photo upload
    $imageName = null;
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES["image"];
        if ($file["error"] === UPLOAD_ERR_OK) {
            $allowed = ["jpg", "jpeg", "png", "gif", "webp"];
            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (!in_array($ext, $allowed)) {
                $errors[] = "Invalid image type. Allowed: JPG, PNG, GIF, WebP.";
            } elseif ($file["size"] > $maxSize) {
                $errors[] = "Image file is too large. Maximum size is 5MB.";
            } else {
                if (!is_dir("uploads")) {
                    mkdir("uploads", 0777, true);
                }
                $imageName = "post_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                $targetPath = "uploads/" . $imageName;
                if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
                    $errors[] = "Failed to save uploaded photo.";
                    $imageName = null;
                }
            }
        } else {
            $errors[] = "An error occurred while uploading the photo.";
        }
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO posts (title, author, category, content, status, image) VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssssss", $title, $author, $category, $content, $status, $imageName);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php?msg=" . urlencode("Post added successfully!"));
            exit;
        } else {
            if ($imageName && file_exists("uploads/" . $imageName)) {
                @unlink("uploads/" . $imageName);
            }
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    } else {
        // Clean up temporary image if form validation had errors
        if ($imageName && file_exists("uploads/" . $imageName)) {
            @unlink("uploads/" . $imageName);
        }
    }
}

include "includes/header.php";
?>

<div class="mb-4">
    <h1 class="page-heading mb-1">Add New Post</h1>
    <div class="post-count-subtitle">Fill in the form below to publish or draft a new article</div>
</div>

<?php if ($errors): ?>
    <div class="alert alert-custom-danger mb-4">
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="table-card p-4">
    <form method="post" enctype="multipart/form-data" id="postForm">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" maxlength="150"
                   placeholder="Enter post title"
                   value="<?= htmlspecialchars($title) ?>" required>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Author</label>
                <input type="text" name="author" class="form-control"
                       placeholder="e.g. Admin"
                       value="<?= htmlspecialchars($author) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control"
                       placeholder="e.g. Technology"
                       value="<?= htmlspecialchars($category) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Draft" <?= $status === "Draft" ? "selected" : "" ?>>Draft</option>
                    <option value="Published" <?= $status === "Published" ? "selected" : "" ?>>Published</option>
                </select>
            </div>
        </div>

        <!-- Add Photo Feature (Reference Design) -->
        <div class="mb-3">
            <label class="form-label d-block">Featured Photo</label>
            <div class="d-flex align-items-center flex-wrap gap-3">
                <label for="imageUpload" class="btn btn-add-photo">
                    <i class="bi bi-image me-2"></i>
                    <span>Add photo</span>
                </label>
                <input type="file" name="image" id="imageUpload" accept="image/png, image/jpeg, image/jpg, image/gif, image/webp" class="d-none">
                <span id="fileNameDisplay" class="text-muted small">No photo chosen</span>
                <button type="button" id="removePhotoBtn" class="btn btn-sm btn-action-delete d-none">
                    <i class="bi bi-trash me-1"></i> Remove photo
                </button>
            </div>

            <!-- Live Image Preview -->
            <div id="imagePreviewContainer" class="mt-3 d-none">
                <div class="photo-preview-wrapper">
                    <img id="imagePreview" src="#" alt="Preview" class="photo-preview-img shadow-sm">
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Content</label>
            <textarea name="content" rows="8" class="form-control"
                      placeholder="Write your blog post content here..."
                      required><?= htmlspecialchars($content) ?></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-purple">Save Post</button>
            <a href="index.php" class="btn btn-action-edit text-decoration-none">Cancel</a>
        </div>
    </form>
</div>

<!-- Image Preview & Selection Script -->
<script>
    (function() {
        const imageUpload = document.getElementById('imageUpload');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewImg = document.getElementById('imagePreview');
        const removeBtn = document.getElementById('removePhotoBtn');

        imageUpload.addEventListener('change', function() {
            const file = this.files && this.files[0];
            if (file) {
                fileNameDisplay.textContent = file.name;
                removeBtn.classList.remove('d-none');

                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                resetPhoto();
            }
        });

        function resetPhoto() {
            imageUpload.value = '';
            fileNameDisplay.textContent = 'No photo chosen';
            previewContainer.classList.add('d-none');
            previewImg.src = '#';
            removeBtn.classList.add('d-none');
        }

        removeBtn.addEventListener('click', resetPhoto);
    })();
</script>

<?php include "includes/footer.php"; ?>
