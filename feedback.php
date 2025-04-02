<?php 
include 'config.php';
include 'sql.php';

if(isset($_GET['id'])){

    $cnum = $_GET['id'];
    $query = "SELECT * FROM reqform WHERE controlnumber = '$cnum'";
    $result = mysqli_query($con, $query);
    
    $row = mysqli_fetch_assoc($result);

    if ($row) {
        $name = $row['ReqPersonel'];

        // Fetching the date finished from the actions table
        $actions_query = "SELECT date_finished FROM actions WHERE controlnumber = '$cnum'";
        $actions_result = mysqli_query($con, $actions_query);
        $actions_row = mysqli_fetch_assoc($actions_result);

        if ($actions_row) {
            $date_finished = $actions_row['date_finished'];
        } else {
            $date_finished = "Not Found";
        }
    } else {
        $name = "Not Found";
        $date_finished = "Not Found";
    }
    
} else {
    header('location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="DENR.png" />
    <style>
        .slider {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 15px;
            border-radius: 5px; 
            background: #d3d3d3;
            outline: none;
            opacity: 0.7;
            -webkit-transition: .2s;
            transition: opacity .2s;
        }

        .slider:hover {
            opacity: 1;
        }

        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background: #1a73e8;
            cursor: pointer;
        }

        .slider::-moz-range-thumb {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background: #1a73e8;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="py-5 text-center">
            <h1>Client Feedback Form</h1>
        </div>
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-body">
                <form action="config.php" method="post">
                    <div class="mb-3">
                        <label for="dateFinished">Date Finished</label>
                        <input type="text" class="form-control" id="dateFinished" name="dateFinished" value="<?php echo htmlspecialchars($date_finished); ?>" readonly>
                        <input type="text" class="form-control" id="controlnumber" name="controlnumber" value="<?php echo htmlspecialchars($cnum); ?>" hidden>
                    </div>
                    <div class="mb-3">
                        <label for="feedbackRating">Feedback Rating</label>
                        <input type="range" class="slider" name="feedbackRating" id="feedbackRating" value="5"  min="1" max="5" required>
                        <div class="d-flex justify-content-between">
                            <span>Poor</span><span>Bellow Satisfactory</span><span>Satisfactory</span><span>Very Satisfactory</span><span>Excellent</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="feedback">Comment</label>
                        <textarea class="form-control" name="feedback" id="feedback" cols="30" rows="5" required></textarea>
                    </div>
                    <button type="submit" name="feedback_btn" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
<!-- 
    
feedback rating: int
comment: text
date finished: date
control number: text


-->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
