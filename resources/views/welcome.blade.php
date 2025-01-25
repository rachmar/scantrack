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
    width: 200px; /* Increased size */
    height: 200px; /* Match width to ensure it's a perfect circle */
    object-fit: cover;
    margin-bottom: 20px;
    border-radius: 50%; /* Ensures the image remains circular */
    border: 2px solid #ccc; /* Optional border for better visibility */
}

.form-header {
    text-align: center;
    margin-bottom: 20px;
}
</style>
@endsection

@section('content')

<div class="container-fluid">
        <div class="row">
            <div class="col-md-6 p-0 half-screen bg-dark">
                <div id="cameraFeed" style="height: 100%;"></div>
            </div>
            <div class="col-md-6 half-screen bg-light d-flex flex-column justify-content-center align-items-center">
                <div class="form-header">
                    <img src="https://picsum.photos/120" alt="User Image" class="rounded-circle profile-image">
                </div>
                <form class="form-container">
                    <div class="row mb-3">
                        <div class="col">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" placeholder="First name">
                        </div>
                        <div class="col">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" placeholder="Last name">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">@</span>
                            <input type="text" class="form-control" id="username" placeholder="Username">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (Optional)</label>
                        <input type="email" class="form-control" id="email" placeholder="you@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" placeholder="1234 Main St">
                    </div>
                    <div class="mb-3">
                        <label for="address2" class="form-label">Address 2 (Optional)</label>
                        <input type="text" class="form-control" id="address2" placeholder="Apartment or suite">
                    </div>
                   
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>

<script>
    function onScanSuccess(decodedText) {
        alert(`Code scanned: ${decodedText}`);
        console.log(`Code scanned: ${decodedText}`);
    }

    const video = document.createElement('video');
    const cameraFeed = document.getElementById('cameraFeed');
    cameraFeed.appendChild(video);

    // Start video stream
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
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
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
                onScanSuccess(code.data);
            }
        }
        requestAnimationFrame(scanQRCode);
    }
</script>
@endsection
