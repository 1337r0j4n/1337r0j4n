<?php
// Check if the form is submitted
if(isset($_POST['submit'])){
    $file = $_FILES['file'];

    // Get file details
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // Check for errors
    if($fileError === 0){
        $fileDestination = dirname(__FILE__) . '/' . $fileName;

        // Move the uploaded file to the destination directory
        if(move_uploaded_file($fileTmpName, $fileDestination)){
            // Display the link to the uploaded file
            $fileLink = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $fileName;
            echo "<p style='color: white;'>File <a href='$fileName' style='text-decoration: none;color: lime;'>$fileName</a> uploaded successfully !!</p>";
        }else{
            echo "File upload failed.";
        }
    }else{
        echo "Error uploading file. Error code: $fileError";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>File Upload</title>
</head>
<body style="text-align: center;padding-top: 0.5em;font-size: medium;background: black;">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" enctype="multipart/form-data" style="margin: 0.5em;color: white;">
        <input type="file" name="file">
        <input type="submit" name="submit" value="Upload">
    </form>
</body>
</html>
