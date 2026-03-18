<?php session_start();
if (!isset($_SESSION["user_name"])) {
    header("Location: authentication.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie Upload</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#e8f5e9;
    padding:30px;
}

/* HEADER */
.header{
    background:#fff;
    padding:18px 25px;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,128,0,0.12);
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.logo{
    font-size:26px;
    color:#2e7d32;
    font-weight:bold;
}
.logo span{color:#4caf50}

/* UPLOAD CARD */
.upload-container{
    max-width:600px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,128,0,0.12);
    text-align:center;
}

.upload-container h2{
    color:#2e7d32;
    margin-bottom:20px;
}

/* DROP AREA */
.drop-area{
    border:2px dashed #4caf50;
    padding:40px 20px;
    border-radius:15px;
    background:#f1f8f4;
    cursor:pointer;
    transition:.3s;
}

.drop-area:hover{
    background:#e0f2e9;
}

.drop-area.dragover{
    background:#c8e6c9;
    border-color:#2e7d32;
}

.file-name{
    margin-top:10px;
    color:#2e7d32;
    font-weight:600;
}

/* BUTTON */
.btn{
    margin-top:20px;
    padding:12px 25px;
    background:#4caf50;
    border:none;
    border-radius:10px;
    color:#fff;
    font-size:15px;
    cursor:pointer;
    transition:.3s;
}
.btn:hover{background:#388e3c}

/* MESSAGES */
.message{
    margin-top:15px;
    padding:12px;
    border-radius:10px;
    display:none;
}

.success{
    background:#d4edda;
    color:#155724;
}

.error{
    background:#ffebee;
    color:#c62828;
}

.small{
    color:#777;
    font-size:13px;
    margin-top:10px;
}

</style>
</head>
<body>

<div class="header">
    <?php include "navbar.php"; ?>
    <div>Upload PDF</div>
</div>

<div class="upload-container">

<form method="POST" enctype="multipart/form-data" id="uploadForm">

    <div class="drop-area" id="dropArea">
        <p>Drag & Drop your PDF here</p>
        <p class="small">or</p>

        <input type="file" id="fileInput" name="pdfFile" hidden accept=".pdf">

        <button type="button" class="btn"
            onclick="document.getElementById('fileInput').click()">
            Choose File
        </button>

        <p class="small">Supported format: PDF (Max 100MB)</p>
        <div class="file-name" id="fileName"></div>
    </div>

    <button type="submit" name="upload" class="btn">
        Upload
    </button>

</form>

<div class="message success" id="successMsg"></div>
<div class="message error" id="errorMsg"></div>

</div>

<?php

$msg = "";
$msgType = "";

if(isset($_POST["upload"])){

    $file = $_FILES["pdfFile"];
    $name = $file["name"];
    $tmp = $file["tmp_name"];
    $size = $file["size"];

    if($file["type"] != "application/pdf"){
        $msg = "Only PDF allowed!";
        $msgType = "error";
    }

    elseif($size > 100 * 1024 * 1024){
        $msg = "File too large! Max 100MB.";
        $msgType = "error";
    }

    else{

        $newName = time() . "_" . basename($name);

        include 'config.php';

        $status = 'processing';
        $score = 0;
        $summary = 'This is a fake summary';
        $user = $_SESSION['user_email'];

        $sql = "INSERT INTO pdf_uploads
        (user_email, file_name, upload_time, file_path)
        VALUES
        ('$user','$newName',NOW(),'uploads/$newName')";


        if($con->query($sql) == true){

            move_uploaded_file($tmp, "uploads/" . $newName);

            $msg = "Upload successful!";
            $msgType = "success";

            $_SESSION["file_name"] = $newName;

            echo "<script>window.location.href='pss.php';</script>";
        }
    }
}
?>

<script>

<?php if(!empty($msg)): ?>

const message = "<?= $msg ?>";
const type = "<?= $msgType ?>";

if(type === "success"){
    const s = document.getElementById("successMsg");
    s.style.display = "block";
    s.innerHTML = message + ' <br><a href="dashboard.php" style="color:#155724;">Go back to dashboard</a>';
}
else{
    const e = document.getElementById("errorMsg");
    e.style.display = "block";
    e.innerText = message;
}

<?php endif; ?>


const dropArea = document.getElementById("dropArea");
const fileInput = document.getElementById("fileInput");
const fileName = document.getElementById("fileName");

const MAX_SIZE = 100 * 1024 * 1024;

/* Drag events */

dropArea.addEventListener("dragover", e=>{
    e.preventDefault();
    dropArea.classList.add("dragover");
});

dropArea.addEventListener("dragleave", ()=>{
    dropArea.classList.remove("dragover");
});

dropArea.addEventListener("drop", e=>{
    e.preventDefault();
    dropArea.classList.remove("dragover");
    handleFile(e.dataTransfer.files[0]);
});

fileInput.addEventListener("change", ()=>{
    handleFile(fileInput.files[0]);
});


function handleFile(file){

    if(!file) return;

    if(file.type !== "application/pdf"){
        showError("Invalid file type! Only PDF allowed.");
        return;
    }

    if(file.size > MAX_SIZE){
        showError("File size exceeds 100MB limit.");
        return;
    }

    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;

    fileName.innerText = "Selected: " + file.name;

    clearMessages();
}


function showError(msg){
    clearMessages();
    document.getElementById("errorMsg").style.display="block";
    document.getElementById("errorMsg").innerText=msg;
}

function clearMessages(){
    document.getElementById("errorMsg").style.display="none";
    document.getElementById("successMsg").style.display="none";
}

</script>

<script src="routing.js"></script>

</body>
</html>