<?php
/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
$conn = mysqli_connect("localhost", "tglevel_support", "Tglevels@123$", "tglevel_support");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
date_default_timezone_set('Asia/Kolkata');
if (isset($_POST['assign_agent']) && isset($_POST['conversation_ids']) && isset($_POST['agent_id'])) {
    $agent_id = mysqli_real_escape_string($conn, $_POST['agent_id']);
    $conversation_ids = $_POST['conversation_ids'];

    $success = true;
    foreach ($conversation_ids as $cid) {
        $cid = mysqli_real_escape_string($conn, $cid);
        $update = mysqli_query($conn, "UPDATE sb_conversations SET agent_id='$agent_id' WHERE id='$cid'");
        if (!$update) {
            $success = false;
        }
    }
    echo $success ? "success" : "error";
    exit;
}
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$filter_sql = "";
if ($start_date != "") {
    $filter_sql .= " AND (SELECT creation_time FROM sb_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) >= '$start_date 00:00:00' ";
}
if ($end_date != "") {
    $filter_sql .= " AND (SELECT creation_time FROM sb_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) <= '$end_date 23:59:59' ";
}

$sql = "SELECT c.id, c.agent_id, u.first_name, u.last_name, u.email, u.profile_image,
        (SELECT message FROM sb_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) AS last_message,
        (SELECT creation_time AS created_at FROM sb_messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) AS last_message_time
        FROM sb_conversations AS c
        LEFT JOIN sb_users AS u ON c.user_id = u.id
        WHERE (c.agent_id IS NULL OR c.agent_id = '')
        $filter_sql
        ORDER BY c.id DESC LIMIT  100";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#566069">
    <title>CRM | TG</title>
    <script src="https://tglevels.org/js/min/jquery.min.js?v=3.8.4"></script>
	<script src="https://tglevels.org//js/main.js?v=3.8.4"></script>
    <link rel="stylesheet" href="https://tglevels.org/css/admin.css?v=3.8.4" media="all">
    <link rel="stylesheet" href="https://tglevels.org/css/responsive-admin.css?v=3.8.4" media="(max-width: 464px)">
    <link rel="shortcut icon" type="image/png" href="https://tglevels.org/uploads/21-10-25/66789_tg-level-icon-1024.png">
	<style>
		body
		{
			font-family: "Support Board Font", "Helvetica Neue", "Apple Color Emoji", Helvetica, Arial, sans-serif;
		}
	</style>
	<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
	.sb-table td {
    white-space: normal;
	}
	</style>
	<style>
		 input#start_date, input#end_date {
    text-align: center;
    font-weight: 500;
    min-width: 255px;
    border-radius: 4px;
    border: 1px solid #d4d4d4;
    outline: none;
    font-size: 13px;
    line-height: 35px;
    height: 35px;
    padding: 0 10px;
    transition: all 0.4s;
    width: 100%;
    min-width: 250px;
    box-sizing: border-box;
    color: #24272a;
    background-color: #fff;
}
	</style>
</head>

<body>
    <div class="sb-main sb-admin">
        <div class="sb-header">
            <div class="sb-admin-nav">
                <img src="https://tglevels.org/uploads/21-10-25/66789_tg-level-icon-1024.png">
                <div>
                    <a id="sb-conversations" class="sb-active" href="https://tglevels.org/">
                        <span>Conversations</span>
                    </a>
                    <a id="sb-users" href="https://tglevels.org/?area=users"><span>Users</span></a>
                    <a id="sb-articles" href="https://tglevels.org/?area=articles"><span>Articles</span></a>
                    <a id="sb-reports" href="https://tglevels.org/?area=reports"><span>Reports</span></a>
                </div>
            </div>
        </div>

        <main>
            <div class="sb-area-users sb-active">
                <div class="sb-top-bar">
                    <div>
                        <h2>Unassigned Conversations</h2>
                    </div>
					<div class="filter-dates">
    <input type="date" id="start_date" value="<?php echo $start_date; ?>">
    <input type="date" id="end_date" value="<?php echo $end_date; ?>">
    <button id="filterBtn" class="sb-btn">Filter</button>
    <button id="clearBtn" class="sb-btn">Clear</button>
</div>


                    <div>
                        <button id="assignMultipleBtn" class="sb-btn">Assign Selected</button>
                    </div>
					
                </div>

                <div class="sb-scroll-area">
                    <table class="sb-table sb-table-users2">
                        <thead>
                            <tr>
                <th style="width:1%"><input type="checkbox" id="selectAll"></th>
                <th>Name</th>
                <th>Message</th>
                <th>Message Time</th>
            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
							<?php if($row['last_message']){ ?>
                    <tr>
                        <td><input type="checkbox" class="conversationCheckbox" value="<?php echo $row['id']; ?>"></td>
						<td class="sb-td-profile"><a class="sb-profile" href="https://tglevels.org/?conversation=<?php echo $row['id'];?>" target="_blank">
    <img loading="lazy" src="<?php echo $row['profile_image'];?>">
    <span><?php echo htmlspecialchars(trim($row['first_name'] . ' ' . $row['last_name'])); ?></span>
</a></td>
                        <td>
<?php
$msg = $row['last_message'] ?? '—';

// Convert escaped newlines
$msg = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $msg);

// Insert line break after every sentence (.!?)
$msg = preg_replace('/([.!?])\s+/u', "$1\n", $msg);

// Optional: Insert break after comma if long
$msg = preg_replace('/,\s+/u', ",\n", $msg);

// Convert to HTML-readable format
echo nl2br(htmlspecialchars($msg));
?>
</td>

                        <td>
<?php 
if (!empty($row['last_message_time'])) {

    $date = new DateTime($row['last_message_time'], new DateTimeZone('UTC'));
    $date->setTimezone(new DateTimeZone('Asia/Kolkata'));

    echo $date->format("M d, Y h:i A");

} else {
    echo '—';
}
?>
</td>
                    </tr>
							<?php } ?>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted">No unassigned conversations found.</td></tr>
            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

<div class="sb-profile-edit-box sb-lightbox sb-agent-admin sb-active" id="assignAgentModal" style="display:none;">
    <div class="sb-info"></div>
    <div class="sb-top-bar">
        <div class="sb-profile">
            <img src="https://tglevels.org/media/user.svg" alt="icon">
            <span class="sb-name">Assign Agent</span>
        </div>
        <div>
            <a class="sb-close sb-btn-icon sb-btn-red" id="closeModal">
                <i class="sb-icon-close"></i>
            </a>
        </div>
    </div>

    <div class="sb-main sb-scroll-area">
        <div class="sb-details">
            <div class="sb-title">Select an Agent</div>
            <div class="sb-edit-box">
                <form id="assignAgentForm">
                    <div  data-type="select" class="sb-input sb-input-select">
                        <span>Agent</span>
                        <select name="agent_id" required id="agent_id" class="form-control select2">
                            <option value="">-- Select Agent --</option>
                            <?php
                            $agents = mysqli_query($conn, "SELECT id, first_name, last_name FROM sb_users WHERE user_type='agent' OR user_type='admin'");
                            while ($agent = mysqli_fetch_assoc($agents)) {
                                echo "<option value='{$agent['id']}'>" . htmlspecialchars($agent['first_name'] . ' ' . $agent['last_name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div style="text-align:right; margin-top:10px;">
                        <button type="submit" class="sb-btn sb-btn-blue">Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    #assignAgentModal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #fff;
        width: 600px;
		height:400px !important;
        max-width: 90%;
        border-radius: 10px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        animation: popup-fade 0.3s ease;
    }

    .sb-lightbox::before {
        /* content: ""; */
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: 9998;
    }

    @keyframes popup-fade {
        from {
            opacity: 0;
            transform: translate(-50%, -45%);
        }
        to {
            opacity: 1;
            transform: translate(-50%, -50%);
        }
    }
    .sb-lightbox
    {
        margin:0px !important;
    }
</style>


    <script>
        $(document).ready(function () {
            // Select all checkbox
            $('#selectAll').on('click', function () {
                $('.conversationCheckbox').prop('checked', this.checked);
            });

            // Open popup for multiple assign
            $('#assignMultipleBtn').on('click', function () {
                var selected = $('.conversationCheckbox:checked');
                if (selected.length === 0) {
                    alert('Please select at least one conversation.');
                    return;
                }
                $('#assignAgentModal').fadeIn();
				 // Trigger manually if you're showing modal via JS
    $('#agent_id').select2({
        dropdownParent: $('#assignAgentModal'),
        placeholder: "-- Select Agent --",
        width: '100%',
        allowClear: true
    });
            });

            // Close modal
            $('#closeModal').on('click', function () {
                $('#assignAgentModal').fadeOut();
            });

            // Assign agent to multiple conversations
            $('#assignAgentForm').on('submit', function (e) {
                e.preventDefault();

                var selectedIds = [];
                $('.conversationCheckbox:checked').each(function () {
                    selectedIds.push($(this).val());
                });

                $.ajax({
                    type: "POST",
                    url: "",
                    data: {
                        assign_agent: 1,
                        conversation_ids: selectedIds,
                        agent_id: $('#agent_id').val()
                    },
                    success: function (res) {
                        if (res.trim() === "success") {
                            alert("Agent assigned successfully!");
                            location.reload();
                        } else {
                            alert("Failed to assign agent.");
                        }
                    }
                });
            });
        });
		$("#filterBtn").on("click", function () {
    let start = $("#start_date").val();
    let end   = $("#end_date").val();

    let url = "?";

    if (start) url += "start_date=" + start + "&";
    if (end)   url += "end_date=" + end;

    window.location.href = url;
});
$("#clearBtn").on("click", function () {
    window.location.href = "?"; // reset filters
});

    </script>
	<script>
		$(document).ready(function () {

    setInterval(function () {
        //location.reload();
    }, 3000);

});
	</script>
</body>

</html>
<?php mysqli_close($conn); ?>