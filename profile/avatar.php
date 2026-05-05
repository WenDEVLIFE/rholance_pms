<?php include '../includes/header.php'; 
include '../includes/sidebar.php';?>
<div class="profile-wrapper">

    <div class="profile-card" style="max-width:400px; flex-direction:column;">

        <h2 style="text-align:center;">Upload Avatar</h2>

        <form action="upload_avatar.php" method="POST" enctype="multipart/form-data">

            <div class="upload-box">
                <input type="file" name="avatar" required>
            </div>

            <button class="btn-primary">Upload</button>

        </form>

    </div>

</div>