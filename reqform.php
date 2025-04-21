<?php
include_once 'config.php';
include 'sql.php';
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="DENR.png" />
    <title>ICT Service Request - DENR</title>
    <style>
        :root {
            --primary-color: #2C6E49;
            --secondary-color: #4C956C;
            --accent-color: #FEFEE3;
            --dark-color: #333;
            --light-color: #f8f9fa;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .container.form-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        
        .form-header {
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        
        .form-header:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background-color: var(--secondary-color);
        }
        
        .form-header h1 {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .form-header p {
            color: #777;
            font-size: 1rem;
        }
        
        .form-section {
            background-color: var(--light-color);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .form-control, .form-select {
            border: 1px solid #ddd;
            padding: 10px 15px;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(76, 149, 108, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-secondary {
            color: #777;
            border-color: #ddd;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 6px;
        }
        
        .hidden {
            display: none;
        }
        
        .footer {
            text-align: center;
            padding: 20px 0;
            color: #777;
            font-size: 0.9rem;
        }
        
        .required-field::after {
            content: "*";
            color: #dc3545;
            margin-left: 4px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container.form-container {
                padding: 20px;
                margin: 20px auto;
            }
            
            .form-header h1 {
                font-size: 1.75rem;
            }
        }
    </style>
    <script>
        function toggleFields() {
            var reqType = document.getElementById("ReqType").value;
            var equipmentFields = document.getElementById("equipmentFields");
            
            if (reqType === "Software" ||  reqType === "Others") {
                equipmentFields.classList.add("hidden");
            } else {
                equipmentFields.classList.remove("hidden");
            }
        }
        
        function clearField(element) {
            if (element.value === "N/A") {
                element.value = "";
            }
        }
        
        function restoreDefault(element) {
            if (element.value === "") {
                element.value = "N/A";
            }
        }
    </script>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top" data-bs-theme="dark">
  <div class="container">
    <a class="navbar-brand" href="index.html">
        <img src="denr.png" alt="DENR Logo" width="40px">
        <span>DENR ICT Services</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="index.html">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="reqstatus.php">Request Status</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#">Contact Support</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container form-container">
    <div class="form-header">
        <h1>ICT Service Request Form</h1>
        <p>Please fill out this form to request technical support from the ICT department</p>
    </div>
    
    <form action="config.php" method="POST">
        <div class="form-section">
            <h4 class="section-title">
                <i class="fas fa-user-circle"></i> Requester Information
            </h4>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="ReqPersonel" class="form-label required-field">Requesting Personnel</label>
                    <input type="text" class="form-control" id="ReqPersonel" name="ReqPersonel" required placeholder="Juan Dela Cruz" autocomplete="off">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="CMail" class="form-label required-field">Email Address</label>
                    <input type="email" class="form-control" id="CMail" name="CMail" required placeholder="juan.delacruz@sample.com" autocomplete="off">
                </div>
            </div>
            <div class="mb-3">
                <label for="Divisions" class="form-label required-field">Division</label>
                <select class="form-select" id="Divisions" name="Divisions" required>
                    <option value="" disabled selected>Select your division</option>
                    <?php
                    $query = "SELECT * FROM `division`";
                    $result = mysqli_query($con, $query);

                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='".$row['divname']."'>".$row['divname']."</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        
        <div class="form-section">
            <h4 class="section-title">
                <i class="fas fa-cogs"></i> Service Request Details
            </h4>
            <div class="mb-3">
                <label for="ReqType" class="form-label required-field">Request Type</label>
                <select class="form-select" id="ReqType" required name="ReqType" onchange="toggleFields()">
                    <option value="" disabled selected>Select request type</option>
                    <option value="Hardware">Hardware Support/Repair</option>
                    <option value="Software">Software Installation/Support</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            
            <div id="equipmentFields" class="hidden">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="BrandModel" class="form-label">Equipment Brand/Model</label>
                        <input type="text" class="form-control" id="BrandModel" name="BrandModel" value="N/A" 
                               onfocus="clearField(this)" onblur="restoreDefault(this)" autocomplete="off">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="SerialNo" class="form-label">Serial Number</label>
                        <input type="text" class="form-control" id="SerialNo" name="SerialNo" value="N/A" 
                               onfocus="clearField(this)" onblur="restoreDefault(this)" autocomplete="off">
                    </div>
                </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ResPerson" class="form-label">Responsible Person</label>
                                <input type="text" class="form-control" id="ResPerson" name="ResPerson" value="N/A" 
                                    onfocus="clearField(this)" onblur="restoreDefault(this)" autocomplete="off">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="SerialNo" class="form-label">Date of Purchase</label>
                                <input type="date" class="form-control" id="SerialNo" name="datepurchase" value="N/A" 
                                    onfocus="clearField(this)" onblur="restoreDefault(this)" autocomplete="off">
                            </div>
                        </div>
                <div class="mb-3">
                    <label for="PropertyNo" class="form-label">Property Number</label>
                    <input type="text" class="form-control" id="PropertyNo" name="PropertyNo" value="N/A" 
                           onfocus="clearField(this)" onblur="restoreDefault(this)" autocomplete="off">
                </div>
                
            </div>
            
            <div class="mb-3">
                <label for="JobDescription" class="form-label required-field">Job Request Description</label>
                <textarea name="JobDescription" id="JobDescription" class="form-control" required
                          style="height: 120px;" placeholder="Please provide detailed information about your request..."></textarea>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="reset" class="btn btn-outline-secondary me-md-2">
                <i class="fas fa-undo"></i> Reset
            </button>
            <button type="submit" name="Submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit Request
            </button>
        </div>
    </form>
</div>

<div class="footer">
    <div class="container">
        <p>Department of Environment and Natural Resources &copy; <?php echo date('Y'); ?> | ICT Unit</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>