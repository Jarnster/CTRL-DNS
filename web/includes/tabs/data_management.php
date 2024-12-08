<h2><i class="fa fa-database"></i> Data Management</h2>
<?php
$csv = file_get_contents($CSV_FILE_PATH);
echo "<b>" . count(array_map("str_getcsv", explode("\n", $csv))) . "</b> total DNS records<br>";
?>
<form method="post">
    <button type="submit" name="delete_all" class="button">Delete ALL</button>
    <button type="submit" name="export_all" class="button">Export CSV Data</button>
</form>

<?php
if (isset($_POST['delete_all'])) {
    file_put_contents($CSV_FILE_PATH, "");
    header('Location: index.php');
    exit;
}

if (isset($_POST['export_all'])) {
    $datetime = date("d-m-Y");
    header('Content-Disposition: attachment; filename="data_backup_' . $datetime . '.csv"');
    header("Content-Type: text/csv");
    readfile($CSV_FILE_PATH);
    exit;
}
?>
