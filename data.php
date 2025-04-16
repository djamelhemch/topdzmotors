<?php
function listFiles($dir, $extensions = []) {
    if (!is_dir($dir)) {
        return []; // Return empty if directory doesn't exist
    }

    $files = array_filter(scandir($dir), function($file) use ($dir, $extensions) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        return in_array(strtolower($ext), $extensions);
    });

    return array_map(function($file) {
        return pathinfo($file, PATHINFO_FILENAME); // Remove extension
    }, array_values($files));
}

$models = listFiles('models', ['glb', 'gltf']);
$wraps = listFiles('textures', ['jpg', 'jpeg', 'png']);

// Debugging output
if (empty($models)) {
    echo json_encode(['error' => 'No models found']);
} else {
    echo json_encode(['models' => $models, 'wraps' => $wraps]);
}
?>