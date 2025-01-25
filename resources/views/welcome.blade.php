@include('layouts.welcome')

<div class="flex-center position-ref full-height">
    @if (Route::has('login'))
    <div class="top-right links color-white">
        @auth
            <a href="{{ url('/admin') }}">Admin</a>
        @else
            <a style="color: white" href="{{ route('login') }}">Login</a>
        @endauth
    </div>
    @endif
    <div class="content">
        <div class="title m-b-md">
            <div class="clockStyle" id="clock"></div>
            <div id="responseDisplay" style="margin-top: 20px; color: white; font-size: 20px;"></div>
        </div>
        <div>
            <input type="text" id="hiddenInput" name="hiddenInput" style="opacity: 0; position: absolute;" autofocus>
        </div>
        
    </div>
</div>

<script>
    let typingTimer; // Timer to detect typing pause
    const doneTypingInterval = 1000; // Time in milliseconds to wait after usheler stops typing
    const hiddenInput = document.getElementById('hiddenInput');
    const responseDisplay = document.getElementById('responseDisplay');

    // Automatically focus the hidden input when any key is pressed
    document.addEventListener('keydown', function () {
        if (document.activeElement !== hiddenInput) {
            hiddenInput.focus();
        }
    });

    // Detect input and start/restart typing timer
    hiddenInput.addEventListener('input', function () {
        clearTimeout(typingTimer); // Reset the timer
        typingTimer = setTimeout(async function () {
            const inputValue = hiddenInput.value;
            try {
                const response = await fetch('/api/endpoint', {
                    method: 'POST', // or 'GET', depending on your API
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', // Pass CSRF token for security
                    },
                    body: JSON.stringify({ inputValue }), // Send input data as JSON
                });
                
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                const result = await response.json(); // Parse JSON response
                
                // Display the API response
                responseDisplay.innerHTML = `
                    <p><strong>Name:</strong> ${result.name || 'N/A'}</p>
                    <p><strong>Course:</strong> ${result.course || 'N/A'}</p>
                `;
            } catch (error) {
                console.error('Error:', error); // Handle errors
                responseDisplay.innerHTML = '<p style="color: red;">Error fetching data</p>';
            }
        }, doneTypingInterval);
    });
</script>
