<?php
// includes/upload_helper.php - shared photo upload handling for create.php and edit.php

/**
 * Validates and saves an uploaded post image into the uploads/ folder.
 *
 * @param array $file    One entry from $_FILES (e.g. $_FILES["image"])
 * @param array $errors  Error list; validation messages are appended to it
 * @return string|null   The saved file name, or null if the upload failed
 */
function save_uploaded_image(array $file, array &$errors): ?string
{
    if ($file["error"] !== UPLOAD_ERR_OK) {
        $errors[] = "An error occurred while uploading the photo.";
        return null;
    }

    $allowed = ["jpg", "jpeg", "png", "gif", "webp"];
    $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($ext, $allowed)) {
        $errors[] = "Invalid image type. Allowed: JPG, PNG, GIF, WebP.";
        return null;
    }
    if ($file["size"] > $maxSize) {
        $errors[] = "Image file is too large. Maximum size is 5MB.";
        return null;
    }
    // Make sure the file really is an image, not just renamed to .jpg
    if (@getimagesize($file["tmp_name"]) === false) {
        $errors[] = "File is not a valid image.";
        return null;
    }

    if (!is_dir("uploads")) {
        mkdir("uploads", 0755, true);
    }

    $imageName = "post_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    if (!move_uploaded_file($file["tmp_name"], "uploads/" . $imageName)) {
        $errors[] = "Failed to save uploaded photo.";
        return null;
    }

    return $imageName;
}
