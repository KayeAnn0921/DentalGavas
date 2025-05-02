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
    }
    .container {
      width: 400px;
      margin: 0 auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px #ccc;
      position: relative;
    }
    h2 {
      text-align: center;
    }
    .close {
      position: absolute;
      top: 10px;
      right: 15px;
      font-weight: bold;
      cursor: pointer;
    }
    .form-group {
      margin-bottom: 10px;
    }
    .form-group label {
      display: inline-block;
      width: 100px;
    }
    .form-group input,
    .form-group select {
      width: calc(100% - 110px);
      padding: 5px;
    }
    .btn-group {
      text-align: center;
      margin-top: 15px;
    }
    button {
      padding: 6px 12px;
      margin: 5px;
      cursor: pointer;
    }
    .top-buttons {
      display: flex;
      justify-content: space-between;
      margin-bottom: 15px;
    }
    .top-buttons div {
      text-align: center;
    }
    .top-buttons button {
      display: block;
      margin: 0 auto 5px;
    }
  </style>
</head>
<body>


<div class="container">
  <span class="close">X</span>
  <h2>Cashiering</h2>

  <div class="top-buttons">
    <div>
      <button>search</button>
      <button>Date</button>
    </div>
    <div>
      <button>Select Patient ^</button>
      <input type="text" placeholder="Text">
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
