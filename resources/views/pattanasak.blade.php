<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pattanasak Jandeang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #fff7ed, #e0f2fe);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            color: #1f2937;
        }

        .profile-container {
            background: white;
            max-width: 780px;
            border-radius: 28px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .profile-image {
            width: 170px;
            height: 170px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fb923c;
            display: block;
            margin: 0 auto 24px;
        }

        h1 {
            font-size: 36px;
            margin-bottom: 12px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 20px;
            margin-bottom: 24px;
        }

        .intro {
            line-height: 1.9;
            font-size: 16px;
            color: #374151;
        }

        .info {
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 28px;
        }

        .tag {
            background: #fed7aa;
            color: #9a5b11;
            padding: 10px 20px;
            border-radius: 999px;
            font-size: 14px;
        }

        .buttons {
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            background: #f97316;
            color: white;
            padding: 12px 28px;
            border-radius: 999px;
            margin: 0 8px;
        }

        .btn-outline {
            background: #1f2937;
        }
    </style>
</head>
<body>
    <section class="profile-container">
        <img class="profile-image" src="/Superman_(STAS).webp" alt="Pattanasak">
        <h1>Pattanasak Jandeang</h1>
        <div class="subtitle">Web Developer & Student</div>
        <p class="intro">
            สวัสดีครับ ผมเป็นนักพัฒนาเว็บไซต์ที่ชอบเรียนรู้เทคโนโลยีใหม่ๆ
            และพัฒนาเว็บไซต์ที่ใช้งานได้จริง ชอบทดลองสร้างสิ่งที่ตอบโจทย์คนใช้งาน
        </p>

        <div class="info">
            <span class="tag">HTML</span>
            <span class="tag">CSS</span>
            <span class="tag">PHP</span>
            <span class="tag">Laravel</span>
        </div>

        <div class="buttons">
            <a class="btn" href="#">ติดต่อผม</a>
            <a class="btn btn-outline" href="/about">กลับหน้า About</a>
        </div>
    </section>
</body>
</html>