<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once '../config/auth_check.php'; // Ensure user is logged in
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tenant') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT la.*, h.title FROM lease_agreements la JOIN houses h ON la.house_id = h.id WHERE la.tenant_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$leases = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Lease Agreements</title>
    <link rel="stylesheet" href="../assets/style1.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>

<body>
    <div class="prop_con">
        <div class="navbar prop_nav">
            <p>Rental.</p>
            <ul>
                <li><a href="../tenant/dashboard.php">Dashboard</a></li>
                <li><a href="lease_agreements.php" class="active">My Lease Agreements</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </div>

        <div class="container">
            <section>
                <h3>My Lease Agreements</h3>
                <table class="user-table" id="leaseAgreementsTable">
                    <thead>
                        <tr>
                            <th>House</th>
                            <th>Signed Date</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($leases)): ?>
                        <?php foreach ($leases as $lease): ?>
                        <tr>
                            <td><?= htmlspecialchars($lease['title']) ?></td>
                            <td><?= $lease['signed_by_tenant'] == 1 ? date("F j, Y", strtotime($lease['signed_at'])) : 'Not signed' ?>
                            </td>
                            <td>
                                <?php if ($lease['signed_by_tenant'] == 1): ?>
                                <button class="btn" onclick="viewLease(<?= $lease['id'] ?>)">View Lease</button>
                                <?php else: ?>
                                <button class="btn" onclick="openModal(<?= $lease['id'] ?>)">View & Sign</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align:center;">No lease agreements found.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <br />
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </section>
        </div>
    </div>

    <!-- Modal container -->
    <div id="leaseModal" class="modal">
        <div class="modal-content" id="modalContent">
            <h3>Lease Agreement</h3>
            <div id="leaseDetails"></div>

            <label id="agreeLabel" style="display:none;">
                <input type="checkbox" id="agreeCheckbox"> I agree to the terms above
            </label>
            <br />

            <button class="btn" id="signBtn" style="display:none;" onclick="signLease()">Sign</button>
            <button class="btn" id="downloadBtn" style="background:green; display:none;"
                onclick="downloadPDF()">Download PDF</button>
            <button class="btn" style="background:gray;" onclick="closeModal()">Close</button>
        </div>
    </div>

    <script>
    let currentLeaseId = null;

    function openModal(leaseId) {
        currentLeaseId = leaseId;
        fetch(`../lease/get_lease_text.php?lease_id=${leaseId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('leaseDetails').innerHTML = html;

                document.getElementById('agreeLabel').style.display = 'block';
                document.getElementById('signBtn').style.display = 'inline-block';
                document.getElementById('downloadBtn').style.display = 'none'; // hide until signed

                document.getElementById('agreeCheckbox').checked = false;
                document.getElementById('leaseModal').style.display = 'block';
            })
            .catch(error => {
                alert("Error loading lease agreement: " + error);
            });
    }

    function viewLease(leaseId) {
        currentLeaseId = leaseId;
        fetch(`../lease/get_lease_details.php?lease_id=${leaseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                const leaseDetailsEl = document.getElementById('leaseDetails');
                leaseDetailsEl.innerHTML = `
                <h4>Lease Text</h4>
                <div>${data.lease_text}</div>
                <h4>Tenant ID Photo</h4>
                ${data.tenant_id_photo_url ? `<img src="${data.tenant_id_photo_url}" alt="Tenant ID Photo" width="200"/>` : '<em>No ID photo available</em>'}
            `;

                // Show download button
                document.getElementById('downloadBtn').style.display = 'inline-block';

                // Hide sign controls
                document.getElementById('agreeLabel').style.display = 'none';
                document.getElementById('signBtn').style.display = 'none';

                document.getElementById('leaseModal').style.display = 'block';
            })
            .catch(err => alert('Failed to load lease details: ' + err));
    }


    function signLease() {
        if (!document.getElementById('agreeCheckbox').checked) {
            alert("Please agree to the terms before signing.");
            return;
        }

        const element = document.getElementById('modalContent');
        const opt = {
            margin: 0.5,
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'in',
                format: 'letter',
                orientation: 'portrait'
            }
        };

        // Generate PDF and get blob
        html2pdf().from(element).set(opt).outputPdf('blob').then(blob => {
            const formData = new FormData();
            formData.append('lease_id', currentLeaseId);
            formData.append('pdf_file', blob, `lease_${currentLeaseId}.pdf`);

            fetch('../lease/sign_lease.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(response => {
                    alert(response);
                    closeModal();
                    location.reload();
                })
                .catch(err => {
                    alert("Error signing lease: " + err);
                });
        });
    }

    function closeModal() {
        currentLeaseId = null;
        document.getElementById('leaseDetails').innerHTML = '';
        document.getElementById('agreeLabel').style.display = 'none';
        document.getElementById('signBtn').style.display = 'none';
        document.getElementById('downloadBtn').style.display = 'none';
        document.getElementById('agreeCheckbox').checked = false;
        document.getElementById('leaseModal').style.display = 'none';
    }

    function downloadPDF() {
        const closeBtn = document.querySelector('#modalContent .btn[onclick="closeModal()"]');
        const downloadBtn = document.getElementById('downloadBtn');

        // Temporarily hide buttons
        closeBtn.style.display = 'none';
        downloadBtn.style.display = 'none';

        const element = document.getElementById('modalContent');
        const opt = {
            margin: 0.5,
            filename: `lease_agreement_${currentLeaseId}.pdf`,
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'in',
                format: 'letter',
                orientation: 'portrait'
            }
        };

        html2pdf().from(element).set(opt).save().then(() => {
            // Restore buttons
            closeBtn.style.display = 'inline-block';
            downloadBtn.style.display = 'inline-block';
        });
    }
    </script>
</body>

</html>