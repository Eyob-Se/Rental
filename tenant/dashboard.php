<?php
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
error_reporting( E_ALL );

include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

if ( !isset( $_SESSION[ 'user_id' ] ) || $_SESSION[ 'role' ] !== 'tenant' ) {
    header( 'Location: ../auth/login.php' );
    exit;
}

$user_id = $_SESSION[ 'user_id' ];

try {
    // Count rental requests
    $stmt = $pdo->prepare( 'SELECT COUNT(*) FROM rental_requests WHERE tenant_id = ?' );
    $stmt->execute( [ $user_id ] );
    $requests_count = $stmt->fetchColumn();

    // Count payments
    $stmt = $pdo->prepare( 'SELECT COUNT(*) FROM transactions WHERE tenant_id = ?' );
    $stmt->execute( [ $user_id ] );
    $payments_count = $stmt->fetchColumn();

    // Fetch rented houses
    $stmt = $pdo->prepare( "
        SELECT h.*
        FROM rental_requests rr
        JOIN houses h ON rr.house_id = h.id
        WHERE rr.tenant_id = ? AND rr.status = 'approved' AND h.is_rented = 1
    " );
    $stmt->execute( [ $user_id ] );
    $rented_houses = $stmt->fetchAll();
} catch ( PDOException $e ) {
    die( 'DB error: ' . $e->getMessage() );
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8' />
    <meta name='viewport' content='width=device-width, initial-scale=1' />
    <title>Tenant Dashboard</title>
    <link rel='stylesheet' href='../assets/style1.css' />
    <link rel='stylesheet' href='../assets/fonts/all.css' />
    <style>
    .rented-houses {
        margin-top: 40px;
        background-color: #1c1c1c;
        padding: 20px;
        border-radius: 8px;
    }

    .rented-houses h2 {
        color: #b2b2b2;
        margin-bottom: 20px;
    }

    .house-card {
        background-color: #2b2b2b;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 6px;
        color: white;
    }

    .house-card h3 {
        color: #f1f1f1;
        margin-bottom: 5px;
    }

    .house-card p {
        margin: 4px 0;
    }
    </style>
</head>

<body>

    <div class='prop_con'>

        <div class='navbar prop_nav'>
            <p>Rental.</p>
            <ul>
                <li><a href='view_houses.php'>View Available Houses</a></li>
                <li><a href='my_requests.php'>My Requests</a></li>
                <li><a href='my_payments.php'>My Payments</a></li>
                <li><a href='lease_agreements.php'>Lease Agreements</a></li>
            </ul>
            <button class='btn' type='button' onclick="window.location.href='../auth/logout.php'">Logout</button>
        </div>

        <div class='container'>
            <h1 style='color: #b2b2b2;'>Welcome, Tenant</h1>
            <p style='color: #ffffff;'>You have submitted <strong>
                    < ?=htmlspecialchars( $requests_count ) ?>
                </strong> rent requests.</p>
            <p style='color: #ffffff;'>You have made <strong>
                    < ?=htmlspecialchars( $payments_count ) ?>
                </strong> payments.</p>

            <div class='rented-houses'>
                <h2>My Rented Houses</h2>

                <?php if ( count( $rented_houses ) > 0 ): ?>
                <?php foreach ( $rented_houses as $house ): ?>
                <div class='house-card'>
                    <?php if ( !empty( $house[ 'image_path' ] ) ): ?>
                    <img src="../uploads/house_images/<?= htmlspecialchars($house['image_path']) ?>" alt='House Image'
                        style='width:100%; max-height:200px; object-fit:cover; border-radius:6px; margin-bottom:10px;'>
                    <?php else: ?>
                    <div
                        style='width:100%; height:200px; background:#444; border-radius:6px; margin-bottom:10px; display:flex; align-items:center; justify-content:center; color:#aaa;'>
                        No image available
                    </div>
                    <?php endif;
?>
                    <h3>
                        < ?=htmlspecialchars( $house[ 'title' ] ) ?>
                    </h3>
                    <p><strong>Location:</strong>
                        < ?=htmlspecialchars( $house[ 'location' ] ) ?>
                    </p>
                    <p><strong>Bedrooms:</strong>
                        < ?=( int )$house[ 'bedrooms' ] ?> | <strong>Bathrooms:</strong>
                            < ?=( int )$house[ 'bathrooms' ] ?>
                    </p>
                    <p><strong>Area:</strong>
                        < ?=htmlspecialchars( $house[ 'area' ] ) ?> sq ft
                    </p>
                    <p><strong>Price:</strong> $< ?=number_format( $house[ 'price' ], 2 ) ?>
                    </p>
                </div>
                <?php endforeach;
?>
                <?php else: ?>
                <p style='color: #ccc;'>You haven't rented any houses yet.</p>
                <?php endif;
?>
            </div>
        </div>

        <footer>
            <div class='footer'>
                <div class='footer-container'>
                    <div class='footer-top'>
                        <p style='color: #2b2d42;
'>&copy;
                            < ?=date( 'Y' ) ?> Rental System. All rights reserved.
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
        </footer>

    </div>
</body>

</html>