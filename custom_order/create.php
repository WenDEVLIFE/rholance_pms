<form method="POST" action="store.php" enctype="multipart/form-data">

<label>Material</label>
<input type="text" name="material" value="<?= htmlspecialchars($itemName) ?>" readonly>

<label>Dimensions</label>
<input type="text" name="dimensions" placeholder="e.g. 5ft x 3ft">

<label>Upload Reference Image</label>
<input type="file" name="reference_image" accept="image/*">

<button type="submit">Submit Custom Order</button>

</form>