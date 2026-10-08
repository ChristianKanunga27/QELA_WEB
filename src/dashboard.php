<?php
session_start();

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.html');
    exit;
}

require_once __DIR__ . '/config.php';
mysqli_set_charset($connection, 'utf8mb4');

$uploadDirectory = __DIR__ . '/uploads';
$uploadUrl = 'uploads/';
$maxImageSize = 5 * 1024 * 1024;
$allowedMimeTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/avif' => 'avif',
];
$types = ['news', 'event', 'announcement', 'agriculture', 'technology'];
$message = '';
$messageType = 'success';
$editingPost = null;

if (empty($_SESSION['dashboard_csrf'])) {
    $_SESSION['dashboard_csrf'] = bin2hex(random_bytes(32));
}

function dashboard_escape($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function dashboard_store_image(array $file, string $directory, int $maxSize, array $mimeTypes): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image upload failed. Please try again.');
    }
    if (($file['size'] ?? 0) > $maxSize || !is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('Choose an image smaller than 5 MB.');
    }
    $imageInfo = @getimagesize($file['tmp_name']);
    $mime = $imageInfo['mime'] ?? '';
    if (!$imageInfo || !isset($mimeTypes[$mime])) {
        throw new RuntimeException('Upload a valid JPG, PNG, WEBP, or AVIF image.');
    }
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The image folder could not be created.');
    }
    if (!is_writable($directory)) {
        throw new RuntimeException('The image folder is not writable by the web server.');
    }
    $name = bin2hex(random_bytes(16)) . '.' . $mimeTypes[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $name)) {
        throw new RuntimeException('The image could not be saved.');
    }
    return $name;
}

function dashboard_remove_image(?string $name, string $directory): void
{
    if ($name && basename($name) === $name) {
        $path = $directory . DIRECTORY_SEPARATOR . $name;
        if (is_file($path)) {
            unlink($path);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedCsrf = $_POST['csrf'] ?? '';
    if (!is_string($submittedCsrf) || !hash_equals($_SESSION['dashboard_csrf'], $submittedCsrf)) {
        http_response_code(400);
        $message = 'Your session check failed. Refresh the page and try again.';
        $messageType = 'error';
    } else {
        $action = $_POST['action'] ?? '';
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        try {
            if ($action === 'delete' && $id) {
                $stmt = mysqli_prepare($connection, 'SELECT image FROM adminDashboard WHERE id = ?');
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                $post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                mysqli_stmt_close($stmt);
                if (!$post) {
                    throw new RuntimeException('That post could not be found.');
                }
                $stmt = mysqli_prepare($connection, 'DELETE FROM adminDashboard WHERE id = ?');
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                dashboard_remove_image($post['image'] ?? null, $uploadDirectory);
                $_SESSION['dashboard_flash'] = 'Post deleted.';
                header('Location: dashboard.php#posts');
                exit;
            }

            if ($action === 'save') {
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $type = $_POST['type'] ?? '';
                if ($title === '' || $description === '' || !in_array($type, $types, true)) {
                    throw new RuntimeException('Enter a title, description, and valid post type.');
                }
                if (mb_strlen($title) > 200) {
                    throw new RuntimeException('Keep the title under 200 characters.');
                }

                $oldImage = null;
                if ($id) {
                    $stmt = mysqli_prepare($connection, 'SELECT image FROM adminDashboard WHERE id = ?');
                    mysqli_stmt_bind_param($stmt, 'i', $id);
                    mysqli_stmt_execute($stmt);
                    $existing = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
                    mysqli_stmt_close($stmt);
                    if (!$existing) {
                        throw new RuntimeException('That post could not be found.');
                    }
                    $oldImage = $existing['image'] ?? null;
                }

                $newImage = dashboard_store_image($_FILES['image'] ?? [], $uploadDirectory, $maxImageSize, $allowedMimeTypes);
                $image = $newImage ?? $oldImage;
                if ($id) {
                    $stmt = mysqli_prepare($connection, 'UPDATE adminDashboard SET title = ?, description = ?, image = ?, type = ? WHERE id = ?');
                    mysqli_stmt_bind_param($stmt, 'ssssi', $title, $description, $image, $type, $id);
                } else {
                    $stmt = mysqli_prepare($connection, 'INSERT INTO adminDashboard (title, description, image, type) VALUES (?, ?, ?, ?)');
                    mysqli_stmt_bind_param($stmt, 'ssss', $title, $description, $image, $type);
                }
                if (!mysqli_stmt_execute($stmt)) {
                    if ($newImage) dashboard_remove_image($newImage, $uploadDirectory);
                    mysqli_stmt_close($stmt);
                    throw new RuntimeException('The post could not be saved. Check the database and try again.');
                }
                mysqli_stmt_close($stmt);
                if ($newImage && $oldImage) dashboard_remove_image($oldImage, $uploadDirectory);
                $_SESSION['dashboard_flash'] = $id ? 'Post updated.' : 'Post published.';
                header('Location: dashboard.php#posts');
                exit;
            }
            throw new RuntimeException('Choose a valid post action.');
        } catch (Throwable $error) {
            error_log('Admin dashboard action failed: ' . $error->getMessage());
            $message = $error instanceof RuntimeException
                ? $error->getMessage()
                : 'The request could not be completed. Check the database configuration and try again.';
            $messageType = 'error';
        }
    }
}

if (!empty($_SESSION['dashboard_flash'])) {
    $message = $_SESSION['dashboard_flash'];
    unset($_SESSION['dashboard_flash']);
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($editId) {
    $stmt = mysqli_prepare($connection, 'SELECT id, title, description, image, type FROM adminDashboard WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $editingPost = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$editingPost) {
        $message = 'That post could not be found.';
        $messageType = 'error';
    }
}

$result = mysqli_query($connection, 'SELECT id, title, description, image, type, date FROM adminDashboard ORDER BY date DESC, id DESC');
if (!$result) {
    http_response_code(500);
    exit('Unable to load posts. Check that the adminDashboard table exists.');
}
$postCount = mysqli_num_rows($result);
?>

<!-- html codes start -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#10271b">
    <title>QELA Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root{color-scheme:light}*{box-sizing:border-box}body{margin:0;background:#f6f7f3;color:#10271b;font:16px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif}a,button,input,textarea,select{font:inherit}button,a{touch-action:manipulation}.layout{min-height:100vh}.sidebar{position:fixed;inset:0 auto 0 0;width:250px;padding:26px 18px;background:#10271b;color:#fff}.brand{display:grid;gap:14px}.logo-card{display:grid;width:fit-content;place-items:center;padding:8px;background:#fff;border-radius:12px}.logo-card img{display:block;width:auto;max-width:150px;height:64px;object-fit:contain}.brand-caption{margin:0;color:#d0d8d1;font-size:.875rem}.sidebar-nav{display:grid;gap:7px;margin-top:28px}.nav-link{display:block;padding:11px 13px;border-radius:10px;color:#f2f5f1;text-decoration:none}.nav-link:hover,.nav-link:focus-visible{background:#243a2d}.nav-link-active{background:#dfa21f;color:#10271b;font-weight:700}.nav-group-label{margin:23px 12px 7px;color:#aebbb1;font-size:.7rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase}.main{margin-left:250px;padding:clamp(20px,4vw,48px);max-width:1600px}.card{background:white;border-radius:16px;padding:clamp(18px,3vw,30px);box-shadow:0 8px 28px #10271b0b}.field{width:100%;border:1px solid #cbd3cc;border-radius:9px;padding:12px 14px;background:#fff;color:#10271b}.field:focus{outline:3px solid #dfa21f55;border-color:#b88512}.table-wrap{overflow-x:auto}.post-table{width:100%;min-width:760px;border-collapse:collapse}.post-table th,.post-table td{padding:14px 12px;text-align:left;vertical-align:top;border-bottom:1px solid #e7ebe6}.post-table th{background:#10271b;color:white}.post-table tbody tr:hover{background:#f8faf7}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:9px 14px;border:0;border-radius:9px;font-weight:700;text-decoration:none;cursor:pointer}.btn-primary{background:#dfa21f;color:#10271b}.btn-danger{background:#b42318;color:white}.alert{padding:14px 16px;border-radius:10px;margin-bottom:20px}.alert-error{background:#fee4e2;color:#8f1d14}.alert-success{background:#e3f5e9;color:#176236}
        @media(max-width:800px){.sidebar{position:static;width:auto;padding:14px 18px}.brand{display:flex;flex-direction:row;align-items:center;justify-content:space-between}.logo-card{width:min(38vw,155px);padding:5px}.logo-card img{height:56px;max-width:150px}.brand-caption{display:none}.sidebar-nav{display:flex;gap:6px;overflow-x:auto;margin-top:12px}.nav-link{white-space:nowrap;padding:9px 12px}.nav-group-label{display:none}.main{margin:0;padding:20px 14px 40px}.header{align-items:flex-start!important;gap:12px}.post-table{min-width:680px}}
        @media(max-width:639px){.logo-card img{height:40px;max-width:105px}}
        @media(max-width:480px){.header{flex-direction:column}.header h2{font-size:1.6rem}.card{border-radius:12px}.post-table{min-width:600px}.post-table th,.post-table td{padding:10px 8px}}
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">
            <a class="logo-card" href="index.html" aria-label="QELA Technologies home">
                <img src="resource/image/qela_logo.png" alt="QELA Technologies Limited">
            </a>
            <p class="brand-caption">Content management</p>
            <a class="nav-link" href="logout.php">Log out</a>
        </div>
 
    </aside>
    <main class="main">
        <header class="header mb-8 flex items-center justify-between"><div><h1 class="text-3xl font-bold">Welcome to QELA</h1><p class="mt-1 text-gray-600">Publish and manage website posts.</p></div><div class="rounded-xl bg-white px-5 py-3 font-semibold shadow-sm"><?= $postCount ?> posts</div></header>
        <?php if ($message !== ''): ?><div class="alert <?= $messageType === 'error' ? 'alert-error' : 'alert-success' ?>" role="status"><?= dashboard_escape($message) ?></div><?php endif; ?>
        <section id="editor" class="card mb-8">
            <div class="mb-5"><h2 class="text-2xl font-bold"><?= $editingPost ? 'Edit post' : 'Create a post' ?></h2><p class="text-gray-600">Uploaded image  must be JPG, PNG, WEBP, or AVIF, up to 5 MB.</p></div>
            <form method="post" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="csrf" value="<?= dashboard_escape($_SESSION['dashboard_csrf']) ?>"><input type="hidden" name="action" value="save">
                <?php if ($editingPost): ?><input type="hidden" name="id" value="<?= (int)$editingPost['id'] ?>"><?php endif; ?>
                <div><label for="title" class="mb-2 block font-semibold">Title</label><input class="field" id="title" name="title" maxlength="200" required value="<?= dashboard_escape($editingPost['title'] ?? '') ?>" placeholder="Enter post title"></div>
                <div><label for="description" class="mb-2 block font-semibold">Description</label><textarea class="field" id="description" name="description" rows="5" required placeholder="Write the post description"><?= dashboard_escape($editingPost['description'] ?? '') ?></textarea></div>
                <div><label for="type" class="mb-2 block font-semibold">Post type</label><select class="field" id="type" name="type" required><option value="">Select type</option><?php foreach ($types as $type): ?><option value="<?= dashboard_escape($type) ?>" <?= ($editingPost['type'] ?? '') === $type ? 'selected' : '' ?>><?= dashboard_escape(ucfirst($type)) ?></option><?php endforeach; ?></select></div>
                <div><label for="image" class="mb-2 block font-semibold">Post image <?= $editingPost ? '(optional; leave blank to keep current image)' : '(optional)' ?></label><input class="field" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp,image/avif"><p class="mt-2 text-sm text-gray-500">The image will be validated and saved with a unique filename.</p><?php if (!empty($editingPost['image'])): ?><img class="mt-3 h-24 w-36 rounded-lg object-cover" src="<?= $uploadUrl . rawurlencode($editingPost['image']) ?>" alt="Current post image"><?php endif; ?></div>
                <div class="flex flex-wrap gap-3"><button class="btn btn-primary" type="submit"><?= $editingPost ? 'Save changes' : 'Publish post' ?></button><?php if ($editingPost): ?><a class="btn bg-gray-100 text-gray-800" href="dashboard.php">Cancel edit</a><?php endif; ?></div>
            </form>
        </section>
        <section id="posts" class="card"><div class="mb-5"><h2 class="text-2xl font-bold">Manage posts</h2><p class="text-gray-600">Edit details or remove published posts.</p></div>
            <?php if ($postCount): ?><div class="table-wrap"><table class="post-table"><thead><tr><th>ID</th><th>Image</th><th>Post</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead><tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?><tr><td><?= (int)$row['id'] ?></td><td><?php if (!empty($row['image'])): ?><img class="h-16 w-20 rounded-lg object-cover" src="<?= $uploadUrl . rawurlencode($row['image']) ?>" alt="<?= dashboard_escape($row['title']) ?>"><?php else: ?><span class="text-gray-400">No image</span><?php endif; ?></td><td class="max-w-sm"><strong><?= dashboard_escape($row['title']) ?></strong><p class="mt-1 text-sm text-gray-600"><?= dashboard_escape(mb_strimwidth($row['description'], 0, 150, '…', 'UTF-8')) ?></p></td><td><span class="rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold"><?= dashboard_escape($row['type']) ?></span></td><td class="whitespace-nowrap text-sm text-gray-600"><?= dashboard_escape($row['date'] ?? '') ?></td><td><div class="flex flex-wrap gap-2"><a class="btn btn-primary" href="dashboard.php?edit=<?= (int)$row['id'] ?>#editor">Edit</a><form method="post" onsubmit="return confirm('Delete this post and its image?')"><input type="hidden" name="csrf" value="<?= dashboard_escape($_SESSION['dashboard_csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn btn-danger" type="submit">Delete</button></form></div></td></tr><?php endwhile; ?>
            </tbody></table></div><?php else: ?><p class="rounded-lg bg-gray-50 p-8 text-center text-gray-600">No posts yet. Create your first post above.</p><?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
