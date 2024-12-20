<?php
session_start();
require_once "./functions/admin.php";
$pageTitle = "Add new book";
require "./template/header.php";
require "./functions/database_functions.php";
$conn = db_connect();

if (isset($_POST['add'])) {
    $isbn = trim($_POST['isbn']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $descr = trim($_POST['descr']);
    $price = floatval(trim($_POST['price']));
    $publisher = trim($_POST['publisher']);
    $image = '';

    // Add image
    if (isset($_FILES['image']) && $_FILES['image']['name'] != "") {
        $image = $_FILES['image']['name'];
        $directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
        $uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . $directory_self . "bootstrap/img/";
        $uploadDirectory .= $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDirectory);
    }

    // Find publisher and return pubid, or create new
    $stmt = $conn->prepare("SELECT publisherid FROM publisher WHERE publisher_name = ?");
    $stmt->bind_param("s", $publisher);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO publisher (publisher_name) VALUES (?)");
        $stmt->bind_param("s", $publisher);
        if (!$stmt->execute()) {
            echo "Can't add new publisher: " . $stmt->error;
            exit;
        }
        $publisherid = $stmt->insert_id;
    } else {
        $row = $result->fetch_assoc();
        $publisherid = $row['publisherid'];
    }

    // Insert book data
    $stmt = $conn->prepare("INSERT INTO books (isbn, title, author, image, descr, price, publisherid) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdsi", $isbn, $title, $author, $image, $descr, $price, $publisherid);
    if (!$stmt->execute()) {
        echo "Can't add new data: " . $stmt->error;
        exit;
    } else {
        header("Location: admin_book.php");
        exit;
    }
}
?>
<form method="post" action="admin_add.php" enctype="multipart/form-data">
    <table class="table">
        <tr>
            <th>ISBN</th>
            <td><input type="text" name="isbn" required></td>
        </tr>
        <tr>
            <th>Title</th>
            <td><input type="text" name="title" required></td>
        </tr>
        <tr>
            <th>Author</th>
            <td><input type="text" name="author" required></td>
        </tr>
        <tr>
            <th>Image</th>
            <td><input type="file" name="image"></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><textarea name="descr" cols="40" rows="5"></textarea></td>
        </tr>
        <tr>
            <th>Price</th>
            <td><input type="text" name="price" required></td>
        </tr>
        <tr>
            <th>Publisher</th>
            <td><input type="text" name="publisher" required></td>
        </tr>
    </table>
    <input type="submit" name="add" value="Add new book" class="btn btn-primary">
    <input type="reset" value="Cancel" class="btn btn-default">
</form>
<br/>
<?php
if (isset($conn)) {
    mysqli_close($conn);
}
require_once "./template/footer.php";
?>
