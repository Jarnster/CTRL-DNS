<?php
// Ensure these functions are only defined once

if (!function_exists('get_device_label')) {
    function get_device_label($ip)
    {
        require __DIR__ . '/config.php';
        if ($DEVICE_LABELS[$ip]) {
            return $DEVICE_LABELS[$ip];
        } else {
            return "None";
        }
    }
}

if (!function_exists('set_config_pwd_hash')) {
    function set_config_pwd_hash($hash)
    {
        $config = json_decode(file_get_contents('../data/config.json'), true);
        $config["ADMIN_PWD_HASH"] = $hash;
        $json = json_encode($config, JSON_PRETTY_PRINT);
        file_put_contents("../data/config.json", $json);
    }
}

// Function to sanitize input
if (!function_exists('sanitize_input')) {
    function sanitize_input($data)
    {
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

// Function to generate DNS log table
if (!function_exists('render_dns_log_table')) {
    function render_dns_log_table($csv)
    {
        $array = array_map("str_getcsv", explode("\n", $csv));
        echo "<div class='widget'>";
        echo count($array) . " total DNS records
    <hr>";
        echo '<div class="table-wrapper">';
        echo '<table class="small-table sortable" data-sortable>
            <thead>
                <tr>
                    <th><i class="fa fa-clock"></i> Time</th>
                    <th><i class="fa fa-user"></i> Source IP (Req)</th>
                    <th><i class="fa fa-laptop"></i> Linked Device (Req)</th>
                    <th><i class="fa fa-globe"></i> Requested Domain</th>
                    <th><i class="fa fa-filter"></i> Record Type (Req)</th>
                    <th><i class="fa fa-bolt"></i> Upstream Used (Rep)</th>
                    <th><i class="fa fa-signal"></i> Answer size (bytes) of Upstream (Rep)</th>
                    <th><i class="fa fa-truck-fast"></i> Latency Time (ms)</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($array as $item) {
            @$time = $item[0];
            @$source_ip_request = $item[1];
            @$linked_device = get_device_label($item[1]);
            @$requested_domain = $item[2];
            @$record_type_request = $item[3];
            @$external_dns_host = $item[4];
            @$external_dns_response_length_bytes = $item[5];

            if ($time && $source_ip_request && $linked_device && $requested_domain && $record_type_request && $record_type_request == "A") {
                echo "<tr>";
                echo "<td data-sort='$time'>" . sanitize_input($time) . "</td>";
                echo "<td data-sort='$source_ip_request'>" . sanitize_input($source_ip_request) . "</td>";
                echo "<td data-sort='$linked_device'>" . sanitize_input($linked_device) . "</td>";
                echo "<td data-sort='$requested_domain'>" . sanitize_input($requested_domain) . "</td>";
                echo "<td data-sort='$record_type_request'>" . sanitize_input($record_type_request) . "</td>";
                echo "<td data-sort='$external_dns_host'>" . sanitize_input($external_dns_host) . "</td>";
                echo "<td data-sort='$external_dns_response_length_bytes'>" . sanitize_input($external_dns_response_length_bytes) . "</td>";
                echo "</tr>";
            }
        }
        echo '</tbody>
        </table>';
        echo '</div>
</div>';
    }
}
