<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="header/header.css">

    <style>
        
    body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    }

    .topnav {
    overflow: hidden;
    background-color: #333;
    }

    .topnav a {
    float: left;
    color: #f2f2f2;
    text-align: center;
    padding: 14px 16px;
    text-decoration: none;
    font-size: 17px;
    }

    .topnav a:hover {
    background-color: #ddd;
    color: black;
    }

    
    .topnav a.split {
    float: right;
    background-color: #04AA6D;
    color: white;
    }
    </style>

<?php
$welcomeUser = isset($_GET['welcome']) ? htmlspecialchars($_GET['welcome']) : '';
?>
</head>


<body>
<div id="toast-container"></div>
<?php if ($welcomeUser): ?>
        <script>
            // Show welcome toast from query param
            document.addEventListener('DOMContentLoaded', function() {
                var toastContainer = document.getElementById('toast-container');
                var toast = document.createElement('div');
                toast.className = 'toast';
                toast.style.cssText = 'background:#27ae60; color:#fff; padding:12px 24px; border-radius:4px; position:fixed; top:30px; right:30px; z-index:9999; font-size:15px; box-shadow:0 2px 8px rgba(0,0,0,0.1);';
                toast.textContent = 'Welcome, <?php echo $welcomeUser; ?>!';
                toastContainer.appendChild(toast);
                setTimeout(function(){
                    if(toastContainer) toastContainer.innerHTML = '';
                }, 3000);
            });
        </script>
<?php else: ?>
        <script>
            // Show welcome toast from cookie if no query param
            function getCookie(name) {
                const value = `; ${document.cookie}`;
                const parts = value.split(`; ${name}=`);
                if (parts.length === 2) return parts.pop().split(';').shift();
                return '';
            }
            document.addEventListener('DOMContentLoaded', function() {
                var username = getCookie('user_name');
                if (username) {
                    var toastContainer = document.getElementById('toast-container');
                    var toast = document.createElement('div');
                    toast.className = 'toast';
                    toast.style.cssText = 'background:#27ae60; color:#fff; padding:12px 24px; border-radius:4px; position:fixed; top:30px; right:30px; z-index:9999; font-size:15px; box-shadow:0 2px 8px rgba(0,0,0,0.1);';
                    toast.textContent = 'Welcome, ' + decodeURIComponent(username) + '!';
                    toastContainer.appendChild(toast);
                    setTimeout(function(){
                        if(toastContainer) toastContainer.innerHTML = '';
                    }, 3000);
                }
            });
        </script>
<?php endif; ?>
<?php include 'header/header.php'; ?>
</body>
<script>
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return '';
}
document.addEventListener('DOMContentLoaded', function() {
    var username = getCookie('user_name');
    var signBtn = document.getElementById('signBtn');
    if (signBtn && username) {
        signBtn.textContent = 'Sign Out';
        signBtn.style.background = '#e74c3c';
        signBtn.style.color = '#fff';
            signBtn.href = 'logout.php';
            // Optionally, you can show a toast before redirecting, but redirect is enough for sign out
    }
});
</script>
</body>
</html>