<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Health Questionnaire</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 0;
      margin: 0;
    }

    .form-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
}


    h1, h2 {
      text-align: center;
    }

    .question-group {
      margin-bottom: 20px;
    }

    .question {
      margin-bottom: 20px;
    }

    label {
      font-weight: bold;
    }

    .condition-list {
      columns: 2;
      -webkit-columns: 2;
      -moz-columns: 2;
      margin-top: 10px;
    }

    .condition-item {
      margin-bottom: 8px;
    }

    .details-input {
      margin-top: 5px;
      margin-left: 10px;
      padding: 5px;
      width: 60%;
      display: none;
    }

    .radio-group {
      margin-top: 5px;
    }
  </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

<div class="form-container">

  <h1>Health Questionnaire</h1>

  <form>

    <div class="question-group">

      <div class="question">
        <label>Are you in good health?</label><br>
        <div class="radio-group">
          <input type="radio" name="good_health" value="Yes"> Yes
          <input type="radio" name="good_health" value="No"> No
        </div>
      </div>

      <div class="question">
        <label>Are you under medical condition right now?</label><br>
        <div class="radio-group">
          <input type="radio" name="medical_condition" value="Yes" onclick="showInput('medical_condition_details')"> Yes
          <input type="radio" name="medical_condition" value="No" onclick="hideInput('medical_condition_details')"> No
        </div>
        <input type="text" id="medical_condition_details" class="details-input" placeholder="If yes, specify...">
      </div>

      <div class="question">
        <label>Have you ever had serious illness or surgical operation?</label><br>
        <div class="radio-group">
          <input type="radio" name="serious_illness" value="Yes" onclick="showInput('serious_illness_details')"> Yes
          <input type="radio" name="serious_illness" value="No" onclick="hideInput('serious_illness_details')"> No
        </div>
        <input type="text" id="serious_illness_details" class="details-input" placeholder="If yes, specify...">
      </div>

      <div class="question">
        <label>Have you ever been hospitalized?</label><br>
        <div class="radio-group">
          <input type="radio" name="hospitalized" value="Yes" onclick="showInput('hospitalized_details')"> Yes
          <input type="radio" name="hospitalized" value="No" onclick="hideInput('hospitalized_details')"> No
        </div>
        <input type="text" id="hospitalized_details" class="details-input" placeholder="If yes, specify...">
      </div>

      <div class="question">
        <label>Are you taking any medication?</label><br>
        <div class="radio-group">
          <input type="radio" name="medication" value="Yes" onclick="showInput('medication_details')"> Yes
          <input type="radio" name="medication" value="No" onclick="hideInput('medication_details')"> No
        </div>
        <input type="text" id="medication_details" class="details-input" placeholder="If yes, specify...">
      </div>

      <div class="question">
        <label>Do you smoke?</label><br>
        <div class="radio-group">
          <input type="radio" name="smoke" value="Yes"> Yes
          <input type="radio" name="smoke" value="No"> No
        </div>
      </div>

      <div class="question">
        <label>Do you use alcohol?</label><br>
        <div class="radio-group">
          <input type="radio" name="alcohol" value="Yes"> Yes
          <input type="radio" name="alcohol" value="No"> No
        </div>
      </div>

      <div class="question">
        <label>Do you use drugs?</label><br>
        <div class="radio-group">
          <input type="radio" name="drugs" value="Yes"> Yes
          <input type="radio" name="drugs" value="No"> No
        </div>
      </div>

      <div class="question">
        <label>Are you allergic to any of the following? (Local Anesthetics, Latex, Penicillin, Aspirin, Others)</label><br>
        <div class="radio-group">
          <input type="radio" name="allergy" value="Yes" onclick="showInput('allergy_details')"> Yes
          <input type="radio" name="allergy" value="No" onclick="hideInput('allergy_details')"> No
        </div>
        <input type="text" id="allergy_details" class="details-input" placeholder="If yes, specify...">
      </div>

      <h2>For Women Only</h2>

      <div class="question">
        <label>Are you pregnant?</label><br>
        <div class="radio-group">
          <input type="radio" name="pregnant" value="Yes"> Yes
          <input type="radio" name="pregnant" value="No"> No
        </div>
      </div>

      <div class="question">
        <label>Are you nursing?</label><br>
        <div class="radio-group">
          <input type="radio" name="nursing" value="Yes"> Yes
          <input type="radio" name="nursing" value="No"> No
        </div>
      </div>

      <div class="question">
        <label>Are you taking birth control pills?</label><br>
        <div class="radio-group">
          <input type="radio" name="birth_control" value="Yes"> Yes
          <input type="radio" name="birth_control" value="No"> No
        </div>
      </div>

    </div>

    <h2>Existing Medical Conditions</h2>

    <div class="condition-list">
      <div class="condition-item"><input type="checkbox" name="condition[]" value="High Blood Pressure"> High Blood Pressure</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Low Blood Pressure"> Low Blood Pressure</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Epilepsy/Convulsions"> Epilepsy/Convulsions</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="AIDS or HIV Infection"> AIDS or HIV Infection</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Sexually Transmitted Disease"> Sexually Transmitted Disease</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Stomach Ulcers"> Stomach Ulcers</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Fainting/Seizures"> Fainting/Seizures</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Rapid Weight Loss"> Rapid Weight Loss</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Joint Replacement"> Joint Replacement</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Heart Surgery"> Heart Surgery</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Heart Attack"> Heart Attack</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Thyroid Problem"> Thyroid Problem</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Heart Disease"> Heart Disease</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Heart Murmur"> Heart Murmur</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Hepatitis/Liver Disease"> Hepatitis/Liver Disease</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Rheumatic Fever"> Rheumatic Fever</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Hay Fever/Allergies"> Hay Fever/Allergies</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Respiratory Problems"> Respiratory Problems</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Hepatitis/Jaundice"> Hepatitis/Jaundice</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Tuberculosis"> Tuberculosis</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Swollen Ankles"> Swollen Ankles</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Kidney Disease"> Kidney Disease</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Diabetes"> Diabetes</div>
      <div class="condition-item"><input type="checkbox" name="condition[]" value="Chest Pain"> Chest Pain</div>
    </div>

    <br><br>
    <div style="text-align:center;">
      <button type="submit" style="padding: 10px 20px; font-size: 16px;">Submit</button>
    </div>

  </form>

</div>

<script>
function showInput(id) {
  document.getElementById(id).style.display = 'inline-block';
}
function hideInput(id) {
  const input = document.getElementById(id);
  input.style.display = 'none';
  input.value = '';
}
</script>

</body>
</html>
