<?php
session_start();
if (!empty($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }
?><!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Shiv Car Rental | Admin Login</title><link rel="stylesheet" href="admin.css"></head>
<body class="login-page"><div class="login-card"><div class="brand">SHIV <span>CAR RENTAL</span></div><h1>Admin Login</h1><p>Manage cars, bookings and payments.</p><form id="loginForm"><label>Email<input type="email" id="email" value="admin@shivcarrental.com" required></label><label>Password<input type="password" id="password" value="Admin@123" required></label><button>Sign in</button><div id="error" class="error"></div></form><small>Demo admin: admin@shivcarrental.com / Admin@123</small></div><script src="admin.js"></script></body></html>
