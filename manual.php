<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Marketing System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            background-color: #f1f1f1;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
        }
        .tab.active {
            background-color: #4CAF50;
            color: white;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"], input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #45a049;
        }
        .dashboard-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }
        .dashboard-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email Marketing System</h1>
        
        <div class="tabs">
            <div class="tab active" data-tab="manual">Manual Email</div>
            <div class="tab" data-tab="automated"><a href="index.php">Automated Campaign</a></div>
        </div>
        
        <div class="tab-content active" id="manual-tab">
            <h2>Manual Email Subscription</h2>
            <form method="POST" action="emailConfiguration.php">
                <div class="form-group">
                    <label for="name">Name (Optional):</label>
                    <input type="text" id="name" name="name" placeholder="Enter your name">
                </div>
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required>
                </div>
                <button type="submit">Subscribe</button>
            </form>
        </div>
        
        <div class="tab-content" id="automated-tab">
            <h2>Automated Email Campaign</h2>
            <p>Manage your automated email campaigns by sending emails to your client list in batches of 400.</p>
            <p>The system will automatically track which clients have received emails and continue from where it left off.</p>
            <a href="autoEmailSender.php" class="dashboard-link">Go to Campaign Dashboard</a>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(t => {
                    t.classList.remove('active');
                });
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show the corresponding tab content
                const tabId = this.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>
