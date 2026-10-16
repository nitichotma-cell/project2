<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติส่วนตัว</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #e0f2fe, #f8fafc);
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .history-card {
            width: min(760px, 100%);
            background: white;
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
        }

        .logo {
            display: block;
            width: 160px;
            height: auto;
            margin: 0 auto 24px;
        }

        .title {
            font-size: 34px;
            color: #1e3a8a;
            margin-bottom: 12px;
        }

        .subtitle {
            color: #475569;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .intro {
            font-size: 16px;
            line-height: 1.9;
            text-align: left;
        }

        .btn-group {
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 999px;
        }
    </style>
</head>
<body>
    <section class="history-card">
        <img class="logo" src="/Superman2025Logo.webp" alt="Superman 2025 Logo">
        <h1 class="title">ประวัติส่วนตัว</h1>
        <div class="subtitle">Pattanasak Jandeang</div>
        <p class="intro">
            ผมเกิดและเติบโตที่ประเทศไทย จบการศึกษาระดับมัธยมปลาย
            และมีความสนใจในการเรียนรู้ด้านเว็บไซต์และการเขียนโปรแกรม
            ผมชอบสร้างสรรค์ผลงานที่สามารถใช้งานได้จริง และพัฒนาทักษะด้าน PHP, HTML, CSS, JavaScript และ Laravel
            โดยมีเป้าหมายเพื่อพัฒนาตนเองให้เป็นนักพัฒนาเว็บที่มีความคิดสร้างสรรค์และตั้งใจทำงานอย่างมืออาชีพ
        </p>

        <div class="btn-group">
            <a class="btn" href="/profile">กลับไปหน้า profile</a>
        </div>
    </section>
</body>
</html>