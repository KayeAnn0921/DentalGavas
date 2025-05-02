<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cashiering</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      margin: 0;
      padding: 0;
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar styling (should match your sidebar.php) */
    .sidebar {
      width: 250px;
      background: #fff;
      box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
      height: 100vh;
      position: fixed;
    }

    /* Main content area */
    .main-content {
      margin-left: 250px;
      padding: 20px;
      flex: 1;
    }

    .container {
      max-width: 800px;
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
    }

    .form-group {
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }

    .form-group label {
      display: inline-block;
      width: 150px;
      font-weight: 500;
      color: #34495e;
    }

    .form-group input,
    .form-group select {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 15px;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
    }

    .btn-group {
      text-align: center;
      margin-top: 25px;
    }

    button {
      padding: 10px 25px;
      margin: 0 10px;
      cursor: pointer;
      border: none;
      border-radius: 4px;
      font-weight: 500;
      font-size: 15px;
      transition: all 0.2s;
    }

    button:nth-child(1) {
      background-color: #95a5a6;
      color: white;
    }

    button:nth-child(1):hover {
      background-color: #7f8c8d;
    }

    button:nth-child(2) {
      background-color: #3498db;
      color: white;
    }

    button:nth-child(2):hover {
      background-color: #2980b9;
    }

    .top-buttons {
      display: flex;
      justify-content: space-between;
      margin-bottom: 25px;
      gap: 15px;
    }

    .top-buttons div {
      flex: 1;
    }

    .top-buttons button {
      width: 100%;
      margin-bottom: 10px;
      padding: 8px;
    }

    .top-buttons input {
      width: 100%;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="container">
    <h2>Cashiering</h2>

    <div class="top-buttons">
      <div>
        <button>Search</button>
        <button>Date</button>
      </div>
      <div>
        <button>Select Patient ▼</button>
        <input type="text" placeholder="Patient name or ID">
      </div>
    </div>

    <div class="form-group">
      <label>Name:</label>
      <input type="text">
    </div>
    <div class="form-group">
      <label>Patient ID:</label>
      <input type="text">
    </div>
    <div class="form-group">
      <label>Procedure:</label>
      <input type="text">
    </div>
    <div class="form-group">
      <label>Amount Charge:</label>
      <input type="text">
    </div>
    <div class="form-group">
      <label>Discount:</label>
      <input type="text">
    </div>
    <div class="form-group">
      <label>Amount Paid:</label>
      <input type="text">
    </div>
    <div class="form-group">
      <label>Balance:</label>
      <input type="text">
    </div>

    <div class="btn-group">
      <button onclick="history.back()">Back</button>
      <button>Save</button>
    </div>
  </div>
</div>

</body>
</html>