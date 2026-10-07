<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Protect dashboard
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.html");
    exit;
}

require_once __DIR__ . "/config.php";

$message = "";
/*
|--------------------------------------------------------------------------
| ADD NEW POST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_post"])) {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $type = trim($_POST["type"]);

    $imageName = null;

    // Check if image was uploaded
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] === 0) {

        $uploadDirectory = __DIR__ . "/uploads/";

        $originalName = $_FILES["image"]["name"];
        $temporaryName = $_FILES["image"]["tmp_name"];

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedExtensions = ["jpg", "jpeg", "png", "webp", "avif"];

        if (in_array($extension, $allowedExtensions)) {

            // Create unique image name
            $imageName = time() . "_" . basename($originalName);

            $destination = $uploadDirectory . $imageName;

            move_uploaded_file($temporaryName, $destination);

        } else {

            $message = "Invalid image type.";

        }
    }

    if ($message === "") {

        $sql = "INSERT INTO adminDashboard 
                (title, description, image, type)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($connection, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $title,
            $description,
            $imageName,
            $type
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Post added successfully.";

        } else {

            $message = "Error: " . mysqli_error($connection);
        }

        mysqli_stmt_close($stmt);
    }
}


/*
|--------------------------------------------------------------------------
| DELETE POST
|--------------------------------------------------------------------------
*/

if (isset($_GET["delete"])) {

    $id = intval($_GET["delete"]);

    // Get image name first
    $sql = "SELECT image FROM adminDashboard WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    mysqli_stmt_bind_param($stmt, "i", $id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $post = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    // Delete image from uploads folder
    if ($post && !empty($post["image"])) {

        $imagePath = __DIR__ . "/uploads/" . $post["image"];

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }


    // Delete database record
    $sql = "DELETE FROM adminDashboard WHERE id = ?";

    $stmt = mysqli_prepare($connection, $sql);

    mysqli_stmt_bind_param($stmt, "i", $id);

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    header("Location: dashboard.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| GET ALL POSTS
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM adminDashboard ORDER BY date DESC";

$result = mysqli_query($connection, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>QELA Admin Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="bg-[#f8f7f3] text-[#10271b]">


<!-- SIDEBAR -->

<aside class="fixed left-0 top-0 h-screen w-64 bg-[#10271b] text-white p-6">

    <div class="mb-10">

        <h1 class="text-2xl font-bold">
            QELA
        </h1>

        <p class="text-sm text-gray-300">
            Admin Dashboard
        </p>

    </div>


    <nav class="space-y-3">

        <a href="dashboard.php"
           class="block rounded-lg bg-[#dfa21f] px-4 py-3 font-semibold text-[#10271b]">
            Dashboard
        </a>

        <a href="#add-post"
           class="block rounded-lg px-4 py-3 hover:bg-[#07130d]">
            Add Post
        </a>

        <a href="#posts"
           class="block rounded-lg px-4 py-3 hover:bg-[#07130d]">
            Manage Posts
        </a>

        <a href="logout.php"
           class="block rounded-lg px-4 py-3 hover:bg-red-700">
            Logout
        </a>

    </nav>

</aside>



<!-- MAIN CONTENT -->

<main class="ml-64 p-8">


    <!-- HEADER -->

    <div class="mb-8 flex items-center justify-between">

        <div>

            <h2 class="text-3xl font-bold">
                Welcome to Admin Dashboard
            </h2>

            <p class="mt-1 text-gray-600">
                Create and manage QELA website posts.
            </p>

        </div>


        <div class="rounded-lg bg-[#dfa21f] px-5 py-3 font-semibold">

            Admin

        </div>

    </div>



    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div class="mb-6 rounded-lg bg-green-100 p-4 text-green-800">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>



    <!-- ADD POST -->

    <section
        id="add-post"
        class="mb-10 rounded-xl bg-white p-6 shadow"
    >

        <div class="mb-6">

            <h3 class="text-2xl font-bold">
                Create New Post
            </h3>

            <p class="text-gray-500">
                Add content that will be displayed on the QELA website.
            </p>

        </div>


        <form
            method="POST"
            enctype="multipart/form-data"
            class="space-y-5"
        >


            <!-- TITLE -->

            <div>

                <label class="mb-2 block font-semibold">
                    Title
                </label>

                <input
                    type="text"
                    name="title"
                    required
                    placeholder="Enter post title"
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 outline-none focus:border-[#dfa21f]"
                >

            </div>



            <!-- DESCRIPTION -->

            <div>

                <label class="mb-2 block font-semibold">
                    Description
                </label>

                <textarea
                    name="description"
                    rows="6"
                    required
                    placeholder="Write your post description..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 outline-none focus:border-[#dfa21f]"
                ></textarea>

            </div>



            <!-- TYPE -->

            <div>

                <label class="mb-2 block font-semibold">
                    Type
                </label>

                <select
                    name="type"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 outline-none focus:border-[#dfa21f]"
                >

                    <option value="">
                        Select type
                    </option>

                    <option value="news">
                        News
                    </option>

                    <option value="event">
                        Event
                    </option>

                    <option value="announcement">
                        Announcement
                    </option>

                    <option value="agriculture">
                        Agriculture
                    </option>

                    <option value="technology">
                        Technology
                    </option>

                </select>

            </div>



            <!-- IMAGE -->

            <div>

                <label class="mb-2 block font-semibold">
                    Image
                </label>

                <input
                    type="file"
                    name="image"
                    accept=".jpg,.jpeg,.png,.webp,.avif"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3"
                >

                <p class="mt-2 text-sm text-gray-500">
                    Supported: JPG, JPEG, PNG, WEBP, AVIF
                </p>

            </div>



            <!-- SUBMIT -->

            <button
                type="submit"
                name="add_post"
                class="rounded-lg bg-[#dfa21f] px-6 py-3 font-bold text-[#10271b] transition hover:bg-[#c89016]"
            >

                + Publish Post

            </button>

        </form>

    </section>



    <!-- POSTS TABLE -->

    <section
        id="posts"
        class="rounded-xl bg-white p-6 shadow"
    >

        <div class="mb-6">

            <h3 class="text-2xl font-bold">
                Manage Posts
            </h3>

            <p class="text-gray-500">
                View and manage content stored in the database.
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full border-collapse">

                <thead>

                    <tr class="bg-[#10271b] text-left text-white">

                        <th class="px-4 py-4">
                            ID
                        </th>

                        <th class="px-4 py-4">
                            Image
                        </th>

                        <th class="px-4 py-4">
                            Title
                        </th>

                        <th class="px-4 py-4">
                            Description
                        </th>

                        <th class="px-4 py-4">
                            Type
                        </th>

                        <th class="px-4 py-4">
                            Date
                        </th>

                        <th class="px-4 py-4">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (mysqli_num_rows($result) > 0): ?>

                    <?php while ($row = mysqli_fetch_assoc($result)): ?>

                        <tr class="border-b hover:bg-gray-50">


                            <!-- ID -->

                            <td class="px-4 py-4 font-semibold">

                                <?php echo $row["id"]; ?>

                            </td>



                            <!-- IMAGE -->

                            <td class="px-4 py-4">

                                <?php if (!empty($row["image"])): ?>

                                    <img
                                        src="uploads/<?php echo htmlspecialchars($row["image"]); ?>"
                                        alt="Post image"
                                        class="h-16 w-20 rounded-lg object-cover"
                                    >

                                <?php else: ?>

                                    <span class="text-gray-400">
                                        No image
                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- TITLE -->

                            <td class="px-4 py-4 font-semibold">

                                <?php echo htmlspecialchars($row["title"]); ?>

                            </td>



                            <!-- DESCRIPTION -->

                            <td class="max-w-xs px-4 py-4 text-gray-600">

                                <?php

                                $description = htmlspecialchars($row["description"]);

                                echo strlen($description) > 100
                                    ? substr($description, 0, 100) . "..."
                                    : $description;

                                ?>

                            </td>



                            <!-- TYPE -->

                            <td class="px-4 py-4">

                                <span class="rounded-full bg-[#dfa21f]/20 px-3 py-1 text-sm font-semibold">

                                    <?php echo htmlspecialchars($row["type"]); ?>

                                </span>

                            </td>



                            <!-- DATE -->

                            <td class="whitespace-nowrap px-4 py-4 text-sm text-gray-500">

                                <?php echo htmlspecialchars($row["date"]); ?>

                            </td>



                            <!-- ACTION -->

                            <td class="px-4 py-4">

                                <a
                                    href="dashboard.php?delete=<?php echo $row["id"]; ?>"
                                    onclick="return confirm('Are you sure you want to delete this post?');"
                                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700"
                                >

                                    Delete

                                </a>

                            </td>


                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="7"
                            class="px-4 py-10 text-center text-gray-500"
                        >

                            No posts have been created yet.

                        </td>

                    </tr>

                <?php endif; ?>


                </tbody>

            </table>

        </div>

    </section>


</main>

</body>
</html>