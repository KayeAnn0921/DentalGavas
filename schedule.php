<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling | Gavas Dental Clinic</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/schedule.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="scheduling-form">
            <h1 class="form-header">Scheduling</h1>
            
            <form>
                <div class="form-group">
                    <label for="visitType">Type of visit:</label>
                    <select id="visitType">
                        <option>appointment</option>
                        <option>walk-in</option>
                        
                    </select>
                </div>

                <div class="form-group">
                    <label for="appointmentDate">Appointment Date:</label>
                    <input type="date" id="appointmentDate" class="calendar-icon">
                </div>

                <div class="form-group">
                    <label for="appointmentTime">Appointment Time:</label>
                    <input type="time" id="appointmentTime">
                </div>

                <div class="form-group">
                    <label for="contactNumber">Contact Number:</label>
                    <input type="text" id="contactNumber" placeholder="e.g. 09123456789">
                </div>

                <div class="form-group">
                    <label for="visitType">Service:</label>
                    <select id="servicetype">
                        <option>surgery</option>
                        <option>otho</option>
                        <option>braces</option>
                    </select>
                </div>

                <button type="submit" class="save-btn">SAVE</button>
            </form>
        </div>
    </div>
</body>
</html>