<h2><i class="fa fa-history"></i> Logs</h2>
<?php
$csv = file_get_contents($CSV_FILE_PATH);
render_dns_log_table($csv);
?>
