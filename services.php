<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services | Gavas Dental Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/services.css">
  
</head>
<body>
<?php
    include 'sidebar.php';
    ?>

<div class="container">
  <h2>Add Dental Services</h2>

  <form id="servicesForm">
    <div class="form-group">
      <label for="mainService">Main Service</label>
      <input type="text" id="mainService" name="mainService" placeholder="e.g. Restorative Treatment">
    </div>
    <div class="form-group">
      <label for="mainServiceAmount">Amount (₱)</label>
      <input type="number" id="mainServiceAmount" name="mainServiceAmount" placeholder="e.g. 1000.00" step="0.01">
    </div>

    <div id="subServicesContainer"></div>

    <button type="button" class="add-btn" onclick="addSubService()">+ Add Sub-Service</button>

    <button type="submit" class="submit-btn">Save Service</button>
  </form>
</div>

<script>
  let subServiceCount = 0;

  function addSubService() {
    subServiceCount++;
    const subServiceId = 'subService_' + subServiceCount;
    const container = document.createElement('div');
    container.className = 'sub-service-group';
    container.id = subServiceId;

    container.innerHTML = `
      <div class="form-group">
        <label>Sub-Service</label>
        <input type="text" name="subServices[]" placeholder="e.g. Light Cure Composite">
      </div>
      <div class="form-group">
        <label>Amount (₱)</label>
        <input type="number" name="subServiceAmounts[]" placeholder="e.g. 800.00" step="0.01">
        <button type="button" class="remove-btn" onclick="removeElement('${subServiceId}')">Remove</button>
      </div>

      <div id="detailedServices_${subServiceCount}"></div>

      <button type="button" class="add-btn" onclick="addDetailedService(${subServiceCount})">+ Add Detailed Service</button>
    `;

    document.getElementById('subServicesContainer').appendChild(container);
  }

  function addDetailedService(subServiceId) {
    const container = document.createElement('div');
    container.className = 'detailed-service-group';

    container.innerHTML = `
      <div class="form-group">
        <label>Detailed Service</label>
        <input type="text" name="detailedServices_${subServiceId}[]" placeholder="e.g. Per Surface">
      </div>
      <div class="form-group">
        <label>Amount (₱)</label>
        <input type="number" name="detailedAmounts_${subServiceId}[]" placeholder="e.g. 500.00" step="0.01">
      </div>
    `;

    document.getElementById('detailedServices_' + subServiceId).appendChild(container);
  }

  function removeElement(id) {
    document.getElementById(id).remove();
  }

  document.getElementById('servicesForm').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Service saved successfully!');
    this.reset();
    document.getElementById('subServicesContainer').innerHTML = '';
  });
</script>

</body>
</html>
