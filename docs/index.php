<?php
// Папка с изображениями
$imageDir = 'docs-image/';

// Получаем все файлы из папки
$images = [];
if (is_dir($imageDir)) {
    $files = scandir($imageDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $path = $imageDir . $file;
            // Проверяем, является ли файл изображением
            if (exif_imagetype($path)) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $images[] = [
                    'path' => $path,
                    'name' => $name
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Документы и грамоты</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #1b1b1b;
            color: #f5f5f5;
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 60px;
            padding-top: 30px;
            position: relative;
        }

        .header::after {
            content: '';
            display: block;
            width: 120px;
            height: 4px;
            background: linear-gradient(90deg, #ff7b00, #ff5500);
            margin: 25px auto 0;
            border-radius: 2px;
        }

        .title {
            font-size: 3.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #ffffff, #e0e0e0);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: 1px;
            text-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
        }

        .subtitle {
            font-size: 1.2rem;
            color: #aaa;
            margin-top: 15px;
            font-weight: 300;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 40px;
            margin-top: 20px;
        }

        .document-card {
            background: #2a2a2a;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .document-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            border-color: #ff7b00;
        }

        .image-container {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #242424;
            position: relative;
        }

        .image-container::before {
            content: '';
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border: 1px solid #444;
            border-radius: 8px;
            pointer-events: none;
        }

        .document-image {
            max-width: 100%;
            max-height: 320px;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease;
        }

        .document-card:hover .document-image {
            transform: scale(1.02);
        }

        .document-name {
            padding: 25px 20px;
            text-align: center;
            background: #252525;
            border-top: 1px solid #333;
            position: relative;
        }

        .document-name::before {
            content: '';
            position: absolute;
            top: -2px;
            left: 20%;
            right: 20%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ff7b00, transparent);
        }

        .document-name h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: #fff;
            margin: 0;
            line-height: 1.4;
        }

        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
            color: #777;
            font-size: 1.3rem;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .gallery {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 30px;
            }
            
            .title {
                font-size: 2.5rem;
            }
            
            .container {
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            .gallery {
                grid-template-columns: 1fr;
            }
            
            .title {
                font-size: 2rem;
            }
            
            .header {
                margin-bottom: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1 class="title">Документы и грамоты</h1>
            <p class="subtitle">Архив достижений и сертификатов</p>
        </header>

        <main>
            <?php if (!empty($images)): ?>
                <div class="gallery">
                    <?php foreach ($images as $image): ?>
                        <div class="document-card">
                            <div class="image-container">
                                <img 
                                    src="<?= htmlspecialchars($image['path']) ?>" 
                                    alt="<?= htmlspecialchars($image['name']) ?>"
                                    class="document-image"
                                    loading="lazy"
                                >
                            </div>
                            <div class="document-name">
                                <h3><?= htmlspecialchars($image['name']) ?></h3>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div>📄</div>
                    <p>В папке docs-image пока нет документов</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.document-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>