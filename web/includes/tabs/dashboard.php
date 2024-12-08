<h2><i class="fa fa-tv"></i> Dashboard</h2>
<div class="dashboard-widgets">
    <div class="widget">
        <p>Version: Early Development Version</p>
    </div>
    <?php
    $csv = file_get_contents($CSV_FILE_PATH);
    render_dns_log_table($csv);
    ?>
</div>
