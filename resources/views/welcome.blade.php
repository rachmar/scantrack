@extends('layouts.welcome')
@section('styles')
<style>
   .half-screen {
   height: 100vh;
   }
   video {
   object-fit: cover;
   width: 100% !important;
   height: 100% !important;
   }
   .form-container {
   text-align: left;
   width: 80%;
   }
   .profile-image {
   width: 450px; /* Increased size */
   height: 450px; /* Match width to ensure it's a perfect circle */
   object-fit: cover;
   border-radius: 50%; /* Ensures the image remains circular */
   border: 2px solid #ccc; /* Optional border for better visibility */
   }
   .form-header {
   text-align: center;
   margin-bottom: 20px;
   }
   .form-control {
   height: 50px; /* Adjust the height as needed */
   font-size: 1.2rem; /* Increase font size for better visibility */
   }
   .bg-ams {
   background-color: #20302d !important;
   }
</style>
@endsection

@section('content')
<div class="container-fluid">
   <div class="row">
      <div class="col-md-6 p-0 half-screen bg-dark">
         <div id="cameraFeed" style="height: 100%;"></div>
      </div>
      <div id="userDetails" class="col-md-6 half-screen d-flex flex-column justify-content-center align-items-center bg-ams">
      </div>
   </div>
</div>
@endsection

@section('scripts')
<script src="{{ URL::asset('assets/js/jsQR.js') }}"></script>

<script>
    let timeoutId; // Variable to store the timeout ID
    let hasScanned = false; // Flag to prevent multiple scans

    // Function to reset the form to its initial state
    function resetForm() {
        $('#userDetails').html(`
            <div class="form-header">
                <img src="/assets/images/blank.jpg" alt="User Image" class="rounded-circle profile-image">
            </div>
            <form class="form-container">
                <div class="mb-3">
                    <label for="id" class="form-label text-white">Scan ID</label>
                    <input type="text" class="form-control" id="student_id" disabled>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label for="firstName" class="form-label text-white">First Name</label>
                        <input type="text" class="form-control" id="firstName" disabled>
                    </div>
                    <div class="col">
                        <label for="lastName" class="form-label text-white">Last Name</label>
                        <input type="text" class="form-control" id="lastName" disabled>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <label for="email" class="form-label text-white">Email</label>
                        <input type="text" class="form-control" id="email" disabled>
                    </div>
                    <div class="col">
                        <label for="phone" class="form-label text-white">Phone Number</label>
                        <input type="text" class="form-control" id="phone" disabled>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="purpose" class="form-label text-white">Purpose</label>
                    <div class="input-group">
                        <textarea type="text" class="form-control" id="purpose" disabled rows="3" />
                    </div>
                </div>
            </form>
        `);
    }

    // Load the form when the page is loaded
    $(document).ready(function () {
        resetForm(); // Reset the form when the page is loaded
    });

    function onScanSuccess(decodedText) {
        console.log(`Code scanned: ${decodedText}`);

        if (decodedText) {
            hasScanned = true; // Set this flag when the scan starts

            $.ajax({
                url: `{{ route('process.show') }}`, // Replace with your endpoint
                type: 'POST',
                data: {
                    code: decodedText,
                    _token: '{{ csrf_token() }}' // Add CSRF token for Laravel
                },
                success: function (response) {
                    console.log('Scan processed successfully:', response);
                    // Assuming response contains the data for the form
                    $('#student_id').val(response.card_id);
                    $('#firstName').val(response.first_name);
                    $('#lastName').val(response.last_name);
                    $('#email').val(response.email);
                    $('#phone').val(response.phone);

                    if (response.image) {
                        const baseUrl = window.location.origin; // Get the base URL dynamically
                        const profileImageUrl = `${baseUrl}/assets/images/${response.image}` || `${baseUrl}/assets/images/student1.jpg`;
                        $('.profile-image').attr('src', profileImageUrl);
                    }

                    if (response.purpose) {
                        $('#purpose').val(response.purpose);
                    }

                    // Clear existing timeout before setting a new one
                    if (timeoutId) {
                        clearTimeout(timeoutId);
                    }
                    timeoutId = setTimeout(resetForm, 5000); // Reset form after 5 seconds
                },
                error: function (error) {
                    console.error('Error processing scan:', error);
                    // On error, disable the form fields and show the error message
                    $('#userDetails').html(`
                        <h3 class="text-center text-white">
                            <p>${error.responseJSON.message}</p>
                        </h3>
                    `);

                    // Clear existing timeout before setting a new one
                    if (timeoutId) {
                        clearTimeout(timeoutId);
                    }
                    timeoutId = setTimeout(resetForm, 5000); // Reset form after 5 seconds
                },
                complete: function () {
                    hasScanned = false; // Set this flag back to false after AJAX completes
                }
            });
        }
    }

    // Start the video stream and QR code scanning
    const video = document.createElement('video');
    const cameraFeed = document.getElementById('cameraFeed');
    cameraFeed.appendChild(video);

    navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
        .then(stream => {
            video.srcObject = stream;
            video.setAttribute('playsinline', true); // Required for iOS
            video.play();
            requestAnimationFrame(scanQRCode);
        })
        .catch(err => {
            console.error("Error accessing the camera:", err);
        });

    function scanQRCode() {
        if (video.readyState === video.HAVE_ENOUGH_DATA && !hasScanned) {
            const canvas = document.createElement('canvas');
            canvas.height = video.videoHeight;
            canvas.width = video.videoWidth;

            const context = canvas.getContext('2d');
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, canvas.width, canvas.height, {
                inversionAttempts: 'dontInvert',
            });

            if (code) {
                hasScanned = true; // Prevent further scans
                onScanSuccess(code.data);
            }
        }
        requestAnimationFrame(scanQRCode);
    }

</script>

@endsection