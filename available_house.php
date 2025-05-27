<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './config/db.php';

// Fetch all approved houses ( publicly visible )
$stmt = $pdo->prepare( "SELECT * FROM houses WHERE status = 'approved' AND is_rented = 0" );
$stmt->execute();
$houses = $stmt->fetchAll();

$isTenant = isset( $_SESSION[ 'user_id' ] ) && $_SESSION[ 'role' ] === 'tenant';
?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8' />
    <meta name='viewport' content='width=device-width, initial-scale=1' />
    <link rel='stylesheet' href='./assets/style1.css' />
    <link rel='stylesheet' href='./assets/fonts/all.css' />
    <title>Available Houses</title>
</head>

<body>
    <div class='prop_con'>

        <!-- Navbar -->
        <div class='navbar prop_nav'>
            <p>Rental.</p>
            <ul>
                <li><a href='index.php'>Home</a></li>
                <li><a href='available_house.php' class='active'>Available Houses</a></li>
                <li><a href='about_us.php'>About us</a></li>
            </ul>
            <button>
                <?php if ( isset( $_SESSION[ 'user_id' ] ) ): ?>
                <a href='./auth/logout.php'>Logout</a>
                <?php else: ?>
                <a href='./auth/login.php'>Login</a>
                <?php endif;
?>
            </button>
        </div>

        <!-- House Cards -->
        <div class='cards'>
            <?php if ( $houses ): ?>
            <?php foreach ( $houses as $house ): ?>
            <div class='card'>
                <img src="./uploads/house_images/<?= htmlspecialchars($house['image_path']) ?>"
                    alt="<?= htmlspecialchars($house['title']) ?>" />
                <h3>
                    <?=htmlspecialchars( $house[ 'title' ] ) ?>
                </h3>
                <h4><i class='fas fa-location-dot'></i>
                    <?=htmlspecialchars( $house[ 'location' ] ) ?>
                </h4>
                <div class='spec'>
                    <p>Bedrooms<br><i class='fas fa-bed'></i>
                        <?=( int )$house[ 'bedrooms' ] ?>
                    </p>
                    <p>Bathrooms<br><i class='fas fa-shower'></i>
                        <?=( int )$house[ 'bathrooms' ] ?>
                    </p>
                    <p>Area<br><i class='fas fa-ruler-combined'></i>
                        <?=htmlspecialchars( $house[ 'area' ] ) ?> sq ft
                    </p>
                </div>
                <p class='price'>ETB<?=number_format( $house[ 'price' ], 2 ) ?>/month</p>
                <?php if ( $isTenant ): ?>
                <form action='request_rent.php' method='post' style='display:inline;'>
                    <input type='hidden' name='house_id' value="<?= (int)$house['id'] ?>">s
                    <button type='submit' class='btn'>Request to Rent</button>
                </form>
                <?php else: ?>
                <a href='./auth/login.php' class='btn'>View Details</a>
                <?php endif;
?>
            </div>
            <?php endforeach;
?>
            <?php else: ?>
            <p>No houses available right now.</p>
            <?php endif;
?>
        </div>

        <!-- Footer -->
        <footer>
            <div class='footer'>
                <div class='footer-container'>
                    <div class='footer-top'>
                        <p style=' color: #2b2d42;'>&copy;
                            <?=date( 'Y' ) ?> Rental System. All rights reserved.
                        </p>
                        <div class='footer-links'>
                            <a href='#'>Privacy Policy</a>
                            <a href='#'>Terms</a>
                            <a href='#'>Contact</a>
                        </div>
                    </div>

                    <div class='footer-social'>
                        <a href='#'><i class='fab fa-facebook-f'></i></a>
                        <a href='#'><i class='fab fa-x-twitter'></i></a>
                        <a href='#'><i class='fab fa-instagram'></i></a>
                        <a href='#'><i class='fab fa-linkedin-in'></i></a>
                    </div>

                    <div class='footer-newsletter'>
                        <form action='#' method='post'>
                            <input type='email' placeholder='Your email address' required>
                            <button type='submit'><i class='fa fa-paper-plane'></i> Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
    </div>
    </footer>

    </div>
</body>

</html>