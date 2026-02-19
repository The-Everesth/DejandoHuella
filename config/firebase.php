<?php

return [
    // path to service account JSON
    'credentials' => base_path(env('FIREBASE_CREDENTIALS')),
    // project id for Firestore REST calls
    'project_id' => env('FIREBASE_PROJECT_ID'),
];
