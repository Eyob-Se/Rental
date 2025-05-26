<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <title>Home Page</title>
</head>
<body>
    <div class="container overlay">

        <div class="navbar">
            <p>Rental.</p>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="available_house.php">Available house</a></li>
                <li><a href="about_us.php">About us</a></li>
            </ul>
            <button type="button" class="btn" onclick="window.location.href='./auth/login.php'">Login</button>
            
        </div>
    
        <div class="herosec">
           <div class="hero">
            <h1>Welcome to <span>Rental System</span></h1>
            <p>
                Find What You Need, When You Need It
            Discover a smarter way to rent from apartments and vehicles to tools and more. Easy, fast, and secure rentals at your fingertips.
            rom real-time availability to seamless booking and tracking, our platform makes renting effortless.
             </p>
             <button type="button" class="btn" onclick="window.location.href='available_house.php'">Explore</button>
           </div>
         </div>
    
        </div>

</body>
</html>