<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Charting System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }
        #sidebar {
            width: 250px;
            background-color: #f0f0f0;
            padding: 20px;
            box-sizing: border-box;
        }
        .main-content {
            flex: 1;
            padding: 20px;
            box-sizing: border-box;
            margin-left: 250px;
            overflow-x: auto;
        }
        .recommendation {
            font-size: 1.4em;
            margin-bottom: 25px;
            font-weight: bold;
            text-align: center;
        }
        table.legend {
            border-collapse: collapse;
            width: 100%;
            max-width: 100%;
            margin: 0 auto 30px;
            border: 2px solid #2196F3; /* Blue border for the table */
            font-size: 1em;
            background-color: #E3F2FD; /* Light blue background */
        }
        table.legend td {
            border: 1px solid #90CAF9; /* Lighter blue for cell borders */
            padding: 8px;
            text-align: left;
        }
        .tooth-row {
            display: flex;
            justify-content: center;
            margin: 15px 0;
            align-items: flex-end;
            gap: 5px;
            flex-wrap: wrap;
        }
        .tooth-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 2px;
            min-width: 50px;
        }
        .tooth-input {
            width: 40px;
            height: 25px;
            margin-bottom: 5px;
            border: 1px solid #90CAF9; /* Blue border */
            border-radius: 4px;
            text-align: center;
            font-size: 0.9em;
        }
        .tooth-img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            margin-bottom: 3px;
        }
        .tooth-number {
            font-size: 12px;
            font-weight: bold;
            margin-top: 3px;
            color: #1565C0; /* Dark blue for tooth numbers */
        }
        .arch-label {
            margin: 8px 0;
            font-weight: bold;
            text-align: center;
            font-size: 1.1em;
            color: #1565C0; /* Dark blue for labels */
        }
        .chart-container {
            text-align: center;
            width: 100%;
            margin: 0 auto;
        }
        .remarks-header {
            color: #1565C0;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 1.1em;
        }
        
        /* Responsive adjustments */
        @media screen and (max-width: 1200px) {
            .tooth-container {
                min-width: 40px;
            }
            .tooth-img {
                width: 40px;
                height: 40px;
            }
        }
        
        @media screen and (max-width: 900px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            #sidebar {
                width: 200px;
            }
            table.legend {
                font-size: 0.9em;
            }
            table.legend td {
                padding: 6px;
            }
        }
        
        @media screen and (max-width: 768px) {
            body {
                flex-direction: column;
            }
            #sidebar {
                width: 100%;
                padding: 10px;
            }
            .main-content {
                margin-left: 0;
            }
            .tooth-container {
                min-width: 35px;
            }
            .tooth-img {
                width: 35px;
                height: 35px;
            }
            .tooth-input {
                width: 30px;
                height: 20px;
                font-size: 0.8em;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="chart-container">
            <div class="remarks-header">Tooth Chart</div>
           
            <div class="remarks">Legend</div>
            <table class="legend">
                <tr>
                    <td><strong>AM</strong> - Amalgam</td>
                    <td><strong>RC</strong> - Recurrent Caries</td>
                    <td><strong>Red</strong> - Caries</td>
                </tr>
                <tr>
                    <td><strong>COM</strong> - Composite</td>
                    <td><strong>RF</strong> - Root Fragment</td>
                    <td><strong>X</strong> - Tooth Indicated for Extraction</td>
                </tr>
                <tr>
                    <td><strong>IMP</strong> - Impacted</td>
                    <td><strong>M</strong> - Missing</td>
                    <td><strong>F</strong> - Tooth Indicated for Filling</td>
                </tr>
                <tr>
                    <td><strong>UN</strong> - Unerupted</td>
                    <td><strong>RCT</strong> - Root Canal Therapy</td>
                    <td><strong>OP</strong> - Oral Prophylaxis</td>
                </tr>
                <tr>
                    <td><strong>LCF</strong> - Lightcured Filled</td>
                    <td><strong>JC</strong> - Jacket Crown</td>
                    <td><strong>RPD</strong> - Removal Partial Denture with casted clasp</td>
                </tr>
                <tr>
                    <td><strong>FB</strong> - Fixedbridge</td>
                    <td><strong>MB</strong> - Maryland Bridge</td>
                    <td><strong>F</strong> - Flouride</td>
                </tr>
            </table>

            <div class="arch-label">Upper Arch</div>
            <div class="tooth-row" id="upper-arch"></div>
            
            <div class="arch-label">Lower Arch</div>
            <div class="tooth-row" id="lower-arch"></div>
        </div>
    </div>

    <script>
        // Function to determine tooth image based on tooth number
        function getToothImage(toothNumber) {
            // Map each valid FDI tooth number to its specific image
            const toothImages = {
                // Upper right (18 to 11)
                '18': 'img/num18.png',
                '17': 'img/num17.png',
                '16': 'img/num16.png',
                '15': 'img/num15.png',
                '14': 'img/num14.png',
                '13': 'img/num13.png',
                '12': 'img/num12.png',
                '11': 'img/num11.png',

                // Upper left (21 to 28)
                '21': 'img/num21.png',
                '22': 'img/num22.png',
                '23': 'img/num23.png',
                '24': 'img/num24.png',
                '25': 'img/num25.png',
                '26': 'img/num26.png',
                '27': 'img/num27.png',
                '28': 'img/num28.png',

                // Lower left (38 to 31)
                '38': 'img/num38.png',
                '37': 'img/num37.png',
                '36': 'img/num36.png',
                '35': 'img/num35.png',
                '34': 'img/num34.png',
                '33': 'img/num33.png',
                '32': 'img/num32.png',
                '31': 'img/num31.png',

                // Lower right (41 to 48)
                '41': 'img/num41.png',
                '42': 'img/num42.png',
                '43': 'img/num43.png',
                '44': 'img/num44.png',
                '45': 'img/num45.png',
                '46': 'img/num46.png',
                '47': 'img/num47.png',
                '48': 'img/num48.png',

                // Default image if number not found
                'default': 'img/tooth.png'
            };

            // Return specific image or default fallback
            return toothImages[toothNumber.toString()] || toothImages['default'];
        }

        function createTooth(containerId, number) {
            const container = document.getElementById(containerId);
            const toothContainer = document.createElement('div');
            toothContainer.className = 'tooth-container';
            
            // Get the appropriate image for this tooth
            const toothImage = getToothImage(number);
            
            toothContainer.innerHTML = `
                <input type="text" class="tooth-input" maxlength="3">
                <img src="${toothImage}" class="tooth-img" alt="Tooth ${number}">
                <div class="tooth-number">${number}</div>
            `;
            container.appendChild(toothContainer);
        }

        // Upper arch (18-28 in one continuous row)
        [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28].forEach(num => {
            createTooth('upper-arch', num);
        });

        // Lower arch (48-38 in one continuous row)
        [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38].forEach(num => {
            createTooth('lower-arch', num);
        });
    </script>
</body>
</html>