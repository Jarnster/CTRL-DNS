<?php
require 'includes/header.php';
require 'includes/utils.php'; // Include utils for reusable functions

// Default Tab content
$allowed_tabs = ['dashboard', 'logs', 'statistics', 'data_management', 'devices', 'security', 'performance', 'dns_entries', 'administrators', 'alerts', 'general_settings', 'advanced_settings'];
$tab = isset($_GET["tab"]) && in_array($_GET["tab"], $allowed_tabs) ? $_GET["tab"] : 'dashboard'; // Default to 'dashboard' if not set

?>

<div class="container">
    <!-- Side Navigation (Tabs) -->
    <div class="sidebar">
        <h2><i class="fa fa-globe"></i> ZenDNS</h2>
        <p style="text-align:center;font-size:12px;">Open-Source DNS Service</p>
        <p style="text-align:center;font-size:14px;">Early Development Version</p>
        <ul>
            <?php
            $tabs = [
                'dashboard' => 'fa-tv',
                'logs' => 'fa-history',
                'statistics' => 'fa-area-chart',
                'data_management' => 'fa-database',
                'devices' => 'fa-laptop',
                'security' => 'fa-shield',
                'performance' => 'fa-tachometer',
                'dns_entries' => 'fa-list',
                'administrators' => 'fa-address-card',
                'alerts' => 'fa-bell',
                'general_settings' => 'fa-cog',
                'advanced_settings' => 'fa-wrench',
            ];

            $tab_titles = [
                'dashboard' => 'Dashboard',
                'logs' => 'Logs',
                'statistics' => 'Statistics',
                'data_management' => 'Data Management',
                'devices' => 'Devices',
                'security' => 'Security',
                'performance' => 'Performance',
                'dns_entries' => 'DNS Entries',
                'administrators' => 'Administrators',
                'alerts' => 'Alerts',
                'general_settings' => 'General Settings',
                'advanced_settings' => 'Advanced Settings',
            ];

            foreach ($tabs as $tab_name => $icon) {
                $active_class = ($tab == $tab_name) ? 'active' : ''; // Check if current tab is active
                $title = isset($tab_titles[$tab_name]) ? $tab_titles[$tab_name] : ucfirst(str_replace('_', ' ', $tab_name)); // Default title with custom format
                echo "<li class='$active_class'><a href='?tab=$tab_name'><i class='fa $icon'></i> $title</a></li>";
            }

            echo "<li><a href='logout.php'><i class='fa fa-sign-out'></i> Logout</a></li>";
            ?>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">

        <?php
        // Handle Tabs
        switch ($tab) {
            case 'dashboard':
                echo "<h2><i class='fa fa-tv'></i> Dashboard</h2>";
                echo '<div class="dashboard-widgets">';

                // System Health widget
                echo "<div class='widget'><p>Version: Early Development Version</p></div>";

                // Display Recent DNS logs
                $csv = file_get_contents($CSV_FILE_PATH);
                render_dns_log_table($csv);

                echo '</div>'; // End of dashboard-widgets
                break;

            case 'logs':
                echo "<h2><i class='fa fa-history'></i> Logs</h2>";
                $csv = file_get_contents($CSV_FILE_PATH);
                render_dns_log_table($csv);
                break;

            case 'data_management':
                echo "<h2><i class='fa fa-database'></i> Data Management</h2>";
                $csv = file_get_contents($CSV_FILE_PATH);
                echo "<b>" . count(array_map("str_getcsv", explode("\n", $csv))) . "</b> total DNS records<br>";
                echo '<form method="post">
                        <button type="submit" name="delete_all">Delete ALL</button>
                        <button type="submit" name="export_all">Export CSV Data</button>
                      </form>';
                break;

            default:
                echo "<h2>Invalid tab!</h2>";
        }
        ?>

        <?php
        // Data Management Actions
        if (isset($_POST['delete_all'])) {
            file_put_contents($CSV_FILE_PATH, ""); // Clear the CSV file
            header('Location: index.php'); // Redirect to avoid form resubmission
        }

        if (isset($_POST['export_all'])) {
            $datetime = date("d-m-Y");
            header('Content-Disposition: attachment; filename="data_backup_' . $datetime . '.csv"');
            header("Content-Type: text/csv");
            header("Content-Length: " . filesize($CSV_FILE_PATH));
            echo file_get_contents($CSV_FILE_PATH); // Output the CSV contents for download
        }
        ?>
    </div>
</div>

</body>

</html>