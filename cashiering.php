<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cashiering</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #f9f9f9;
      display: flex;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }
    .container {
      width: 700px; /* Expanded from 400px to 700px */
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      position: relative;
    }
    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #2c3e50;
    }
    .close {
      position: absolute;
      top: 15px;
      right: 20px;
      font-weight: bold;
      cursor: pointer;
      font-size: 18px;
      color: #7f8c8d;
    }
    .close:hover {
      color: #e74c3c;
    }
    .form-group {
      margin-bottom: 15px;
      display: flex;
      align-items: center;
    }
    .form-group label {
      display: inline-block;
      width: 150px; /* Increased from 100px */
      font-weight: 500;
      color: #34495e;
    }
    .form-group input,
    .form-group select {
      width: calc(100% - 160px); /* Adjusted for new label width */
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
<div class="container">
  <span class="close">X</span>
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

</body>
</html>