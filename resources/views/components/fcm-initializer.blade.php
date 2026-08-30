@auth
@php
    try {
        $fbSettings = \App\Models\Setting::whereIn('key', [
            'firebase_project_id', 'firebase_api_key', 'firebase_auth_domain',
            'firebase_storage_bucket', 'firebase_messaging_sender_id', 'firebase_app_id',
            'firebase_vapid_key', 'firebase_enabled'
        ])->pluck('value', 'key');
    } catch (\Exception $e) {
        $fbSettings = collect();
    }
@endphp

@if(($fbSettings['firebase_enabled'] ?? '1') == '1' && !empty($fbSettings['firebase_api_key']) && !empty($fbSettings['firebase_project_id']))
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!('serviceWorker' in navigator) || !('Notification' in window)) {
        return;
    }

    const firebaseConfig = {
        apiKey: @json($fbSettings['firebase_api_key'] ?? ''),
        authDomain: @json($fbSettings['firebase_auth_domain'] ?? ''),
        projectId: @json($fbSettings['firebase_project_id'] ?? ''),
        storageBucket: @json($fbSettings['firebase_storage_bucket'] ?? ''),
        messagingSenderId: @json($fbSettings['firebase_messaging_sender_id'] ?? ''),
        appId: @json($fbSettings['firebase_app_id'] ?? '')
    };

    try {
        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function(registration) {
            messaging.useServiceWorker(registration);

            Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    const vapidKey = @json($fbSettings['firebase_vapid_key'] ?? '');
                    const tokenOptions = vapidKey ? { vapidKey: vapidKey } : {};

                    messaging.getToken(tokenOptions).then(function(currentToken) {
                        if (currentToken) {
                            saveFcmTokenOnLogin(currentToken);
                        }
                    }).catch(function(err) {
                        console.log('FCM token retrieve notice:', err.message);
                    });
                }
            });
        }).catch(function(err) {
            console.log('FCM SW registration notice:', err.message);
        });

        // Handle foreground notifications
        messaging.onMessage(function(payload) {
            if (payload.notification) {
                const title = payload.notification.title || 'IOM Notification';
                const options = {
                    body: payload.notification.body || '',
                    icon: payload.notification.icon || '/images/logo.png',
                    image: payload.notification.image || null
                };
                new Notification(title, options);
            }
        });
    } catch(e) {
        console.log('FCM Init notice:', e.message);
    }
});

function saveFcmTokenOnLogin(token) {
    const lastSaved = localStorage.getItem('fcm_token_last_saved');
    if (lastSaved === token) return; // avoid duplicate requests

    fetch('/user/fcm-token', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            fcm_token: token,
            device_type: 'web'
        })
    }).then(res => res.json()).then(data => {
        if (data.success) {
            localStorage.setItem('fcm_token_last_saved', token);
        }
    }).catch(err => console.log('FCM token save error:', err));
}
</script>
@endif
@endauth
