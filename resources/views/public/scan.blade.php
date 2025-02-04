@extends('layouts.welcome')
@section('styles')
<style>
    .full-screen {
        height: 100vh;
        width: 100vw;
        overflow: hidden;
        position: relative;
    }
    video {
        object-fit: cover;
        width: 100% !important;
        height: 100% !important;
    }
</style>
@endsection

@section('content')
<div id="cameraFeed" class="full-screen"></div>
@endsection

@section('scripts')
<script src="{{ URL::asset('assets/js/jsQR.js') }}"></script>
<script src="{{ URL::asset('assets/js/sweetalert.min.js') }}"></script>

<script>
    let isProcessing = false; // Flag to prevent multiple scans while waiting for AJAX result
    let scanCooldown = false; // Cooldown flag to prevent rapid scans

    function onScanSuccess(decodedText) {
        console.log(`Code scanned: ${decodedText}`);

        if (decodedText && !isProcessing) {
            isProcessing = true; // Set this flag when the scan starts

            $.ajax({
                url: `{{ route('public.scan.show') }}`, // Replace with your endpoint
                type: 'POST',
                data: {
                    code: decodedText,
                    _token: '{{ csrf_token() }}' // Add CSRF token for Laravel
                },
                success: function (response) {
                    console.log('Scan processed successfully:', response);

                    Swal.fire({
                        icon: 'success',
                        title: 'Scan Successful',
                        text: `Welcome ${response.first_name} ${response.last_name}!`,
                        timer: 5000,
                        timerProgressBar: true
                    });
                },
                error: function (error) {
                    console.error('Error processing scan:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Scan Failed',
                        text: error.responseJSON.message || 'An error occurred',
                        timer: 5000,
                        timerProgressBar: true
                    });
                },
                complete: function () {
                    isProcessing = false; // Allow future scans
                    scanCooldown = true; // Activate cooldown
                    setTimeout(() => {
                        scanCooldown = false; // Disable cooldown after 2 seconds
                    }, 5000);
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

            Swal.fire({
                icon: 'error',
                title: 'Camera Error',
                text: 'Unable to access the camera.',
                confirmButtonText: 'OK'
            });
        });

    function scanQRCode() {
        if (video.readyState === video.HAVE_ENOUGH_DATA && !isProcessing && !scanCooldown) {
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
