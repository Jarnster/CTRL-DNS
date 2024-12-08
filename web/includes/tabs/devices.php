<h2><i class="fa fa-laptop"></i> Devices</h2>
<div class="dashboard-widgets">
    <div class="widget">
        <h3>Most Active Devices</h3>
        <?php foreach ($DEVICES as $ip => $label): ?>
            <p><?php echo htmlspecialchars($ip) . " - " . htmlspecialchars($label); ?></p>
        <?php endforeach; ?>
    </div>
    <div class="widget">
        <h3>Device Management</h3>
        <button class="button" onclick="openModal()">Create</button>
    </div>
</div>

<!-- Modal -->
<div id="createDeviceModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Create New Device</h3>
        <form method="post">
            <label for="device_ip">Device IP Address:</label>
            <input type="text" id="device_ip" name="device_ip" required>
            
            <label for="device_label">Device Label:</label>
            <input type="text" id="device_label" name="device_label" required>
            
            <button type="submit" name="add_device" class="button">Add Device</button>
        </form>
    </div>
</div>

<?php
// Handle Form Submission
if (isset($_POST['add_device'])) {
    $new_ip = $_POST['device_ip'];
    $new_label = $_POST['device_label'];

    if (filter_var($new_ip, FILTER_VALIDATE_IP)) {
        $DEVICES[$new_ip] = $new_label;
        // TODO: Update $DEVICES in json config
        echo "<script>alert('Device added successfully!');</script>";
    } else {
        echo "<script>alert('Invalid IP address!');</script>";
    }
}
?>

<script>
function openModal() {
    document.getElementById("createDeviceModal").style.display = "block";
}

function closeModal() {
    document.getElementById("createDeviceModal").style.display = "none";
}

window.onclick = function(event) {
    var modal = document.getElementById("createDeviceModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
};
</script>
