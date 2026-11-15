<?php
// edit.php - UPDATE (edit existing post with photo support)
require_once "db_connect.php";
$pageTitle = "Edit Post";
$errors = [];

$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);

$stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$post) {
    header("Location: index.php?msg=" . urlencode("Post not found."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"] ?? "");
    $author = trim($_POST["author"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $status = $_POST["status"] ?? "Draft";
    $removePhoto = isset($_POST["remove_photo"]) && $_POST["remove_photo"] === "1";

    if ($title === "")
        $errors[] = "Title is required.";
    if ($author === "")
        $errors[] = "Author is required.";
    if ($category === "")
        $errors[] = "Category is required.";
    if ($content === "")
        $errors[] = "Content is required.";
    if (!in_array($status, ["Draft", "Published"]))
        $status = "Draft";

    $oldImage = $post["image"];   // photo currently saved in the database
    $imageName = $oldImage;        // photo that will be saved
    $uploadedNew = null;             // newly uploaded file (deleted again if saving fails)

    // Handle new photo upload
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
            } elseif (@getimagesize($file["tmp_name"]) === false) {
                $errors[] = "File is not a valid image.";
            } else {
                if (!is_dir("uploads")) {
                    mkdir("uploads", 0777, true);
                }
                $newImageName = "post_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                $targetPath = "uploads/" . $newImageName;
                if (move_uploaded_file($file["tmp_name"], $targetPath)) {
                    $uploadedNew = $newImageName;
                    $imageName = $newImageName;   // old file is NOT deleted yet
                } else {
                    $errors[] = "Failed to save uploaded photo.";
                }
            }
        } else {
            $errors[] = "An error occurred while uploading the photo.";
        }
    } elseif ($removePhoto) {
        $imageName = null;                          // old file is NOT deleted yet
    }

    // keep what the user typed if there are errors
    $post = array_merge($post, compact("title", "author", "category", "content", "status"));

    if (empty($errors)) {
        $stmt = mysqli_prepare(
            $conn,
            "UPDATE posts SET title=?, author=?, category=?, content=?, status=?, image=? WHERE id=?"
        );
        mysqli_stmt_bind_param($stmt, "ssssssi", $title, $author, $category, $content, $status, $imageName, $id);

        if (mysqli_stmt_execute($stmt)) {
            // Update succeeded, so it is now safe to remove the old photo file
            if (!empty($oldImage) && $oldImage !== $imageName && file_exists("uploads/" . $oldImage)) {
                @unlink("uploads/" . $oldImage);
            }
            header("Location: index.php?msg=" . urlencode("Post updated successfully!"));
            exit;
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }

    // Something failed: discard the newly uploaded file so no orphan is left behind
    if (!empty($errors) && $uploadedNew && file_exists("uploads/" . $uploadedNew)) {
        @unlink("uploads/" . $uploadedNew);
    }
}

include "includes/header.php";
?>

<div class="mb-4">
    <h1 class="page-heading mb-1">Edit Post #<?= $post["id"] ?></h1>
    <div class="post-count-subtitle">Modify the details of this blog post</div>
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
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $post["id"] ?>">

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" maxlength="150"
                value="<?= htmlspecialchars($post["title"]) ?>" required>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Author</label>
                <input type="text" name="author" class="form-control" value="<?= htmlspecialchars($post["author"]) ?>"
                    required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control"
                    value="<?= htmlspecialchars($post["category"]) ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Draft" <?= $post["status"] === "Draft" ? "selected" : "" ?>>Draft</option>
                    <option value="Published" <?= $post["status"] === "Published" ? "selected" : "" ?>>Published</option>
                </select>
            </div>
        </div>

        <!-- Featured Photo Section -->
        <div class="mb-3">
            <label class="form-label d-block">Featured Photo</label>

            <?php if (!empty($post["image"]) && file_exists("uploads/" . $post["image"])): ?>
                <div id="currentPhotoContainer" class="mb-3">
                    <div class="photo-preview-wrapper mb-2">
                        <img src="uploads/<?= htmlspecialchars($post["image"]) ?>" alt="Current photo"
                            class="photo-preview-img shadow-sm">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhotoCheck">
                        <label class="form-check-label text-danger small fw-semibold" for="removePhotoCheck">
                            <i class="bi bi-trash me-1"></i> Delete this photo
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <div class="d-flex align-items-center flex-wrap gap-3">
                <label for="imageUpload" class="btn btn-add-photo">
                    <i class="bi bi-image me-2"></i>
                    <span><?= !empty($post["image"]) ? "Change photo" : "Add photo" ?></span>
                </label>
                <input type="file" name="image" id="imageUpload"
                    accept="image/png, image/jpeg, image/jpg, image/gif, image/webp" class="d-none">
                <span id="fileNameDisplay" class="text-muted small">No new photo chosen</span>
                <button type="button" id="removePhotoBtn" class="btn btn-sm btn-action-delete d-none">
                    <i class="bi bi-x-circle me-1"></i> Cancel selection
                </button>
            </div>

            <!-- New Image Preview -->
            <div id="imagePreviewContainer" class="mt-3 d-none">
                <div class="photo-preview-wrapper">
                    <img id="imagePreview" src="#" alt="Preview" class="photo-preview-img shadow-sm">
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Content</label>
            <textarea name="content" rows="8" class="form-control"
                required><?= htmlspecialchars($post["content"]) ?></textarea>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-purple">Update Post</button>
            <a href="index.php" class="btn btn-action-edit text-decoration-none">Cancel</a>
        </div>
    </form>
</div>

<!-- Image Preview Script -->
<script>
    (function () {
        const imageUpload = document.getElementById('imageUpload');
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const previewContainer = document.getElementById('imagePreviewContainer');
        const previewImg = document.getElementById('imagePreview');
        const removeBtn = document.getElementById('removePhotoBtn');

        imageUpload.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (file) {
                fileNameDisplay.textContent = file.name;
                removeBtn.classList.remove('d-none');

                const reader = new FileReader();
                reader.onload = function (e) {
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
            fileNameDisplay.textContent = 'No new photo chosen';
            previewContainer.classList.add('d-none');
            previewImg.src = '#';
            removeBtn.classList.add('d-none');
        }

        removeBtn.addEventListener('click', resetPhoto);
    })();
</script>

<?php include "includes/footer.php"; ?>