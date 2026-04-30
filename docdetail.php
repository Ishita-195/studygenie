<?php session_start();
if (!isset($_SESSION["user_name"])) {
    header("Location: authentication.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudyGenie Document</title>

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
    margin-bottom:25px;
}

.logo{
    font-size:26px;
    color:#2e7d32;
    font-weight:bold;
}
.logo span{color:#4caf50}

/* CARD */
.card{
    max-width:700px;
    margin:auto;
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,128,0,0.12);
}

/* INFO */
.info{
    margin-bottom:25px;
}

.file-name{
    font-size:22px;
    color:#2e7d32;
    font-weight:600;
    margin-bottom:8px;
}

.meta{
    color:#666;
    margin-bottom:8px;
}

/* ACTION BUTTONS */
.actions{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:15px;
    margin-top:20px;
}

.btn{
    padding:14px;
    border-radius:12px;
    font-size:15px;
    text-decoration:none;
    border:none;
    transition:.3s;
    cursor:pointer;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* PRIMARY ACTIONS */
.primary{
    background:#4caf50;
    color:white;
}
.primary:hover{background:#388e3c}

/* SECONDARY */
.secondary{
    background:#f1f8f4;
    color:#2e7d32;
}
.secondary:hover{background:#dcedc8}

/* DELETE */
.delete{
    background:#ffebee;
    color:#c62828;
}
.delete:hover{background:#ffcdd2}

iframe{
    margin-top:25px;
    border-radius:10px;
}

</style>
</head>
<body>

<div class="header">
    <?php include "navbar.php"; ?>
    <div>Document Details</div>
</div>

<?php

include 'config.php';

$id = $_GET['id'];

$sql = "SELECT file_name, upload_time, file_path FROM pdf_uploads WHERE id='$id'";
$result = $con->query($sql);
$row = $result->fetch_assoc();

?>

<div class="card">

    <!-- DOCUMENT INFO -->
    <div class="info">
        <div class="file-name"><?php echo $row['file_name']; ?></div>

        <div class="meta">
            Uploaded:
            <?php echo date("d M Y • h:i A", strtotime($row['upload_time'])); ?>
        </div>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="actions">

        <a href="ai.php?id=<?php echo $id; ?>" class="btn primary">
            View Summary
        </a>

        <a href="qa.php?id=<?php echo $id; ?>" class="btn secondary">
            View Analysis
        </a>

        <a href="quiz.php?id=<?php echo $id; ?>" class="btn secondary">
            Generate Quiz
        </a>

        <form method="post" onsubmit="return confirmDelete();">
            <button type="submit" class="btn delete" name="delete_file">
                Delete File
            </button>
        </form>

    </div>

    <!-- PDF PREVIEW -->
    <h3 style="margin-top:30px;color:#2e7d32;">Document Preview</h3>

    <iframe 
        src="uploads/<?php echo $row['file_name']; ?>" 
        width="100%" 
        height="600px">
    </iframe>

</div>

<?php

if(isset($_POST['delete_file'])){

    $file_to_delete = 'uploads/' . $row['file_name'];

    $sql = "DELETE FROM pdf_uploads WHERE id='$id'";

    if($con->query($sql)){

        if(file_exists($file_to_delete)){
            unlink($file_to_delete);
        }

        echo '<script>
        alert("File deleted successfully!");
        window.location="dashboard.php";
        </script>';

        exit();
    }
}

?>

<script>

function confirmDelete(){
    return confirm("Are you sure you want to delete this file?");
}

</script>

<script src="routing.js"></script>

</body>
</html>