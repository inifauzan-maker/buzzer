<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Maintenance</title>
        <style>
            body {
                margin: 0;
                font-family: "Aptos", "Segoe UI", Arial, sans-serif;
                background: #0a0a5c;
                color: #fff;
                display: grid;
                place-items: center;
                min-height: 100vh;
                text-align: center;
                padding: 24px;
            }
            .card {
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.2);
                padding: 24px;
                border-radius: 18px;
                max-width: 520px;
                width: 100%;
            }
            h1 {
                margin: 0 0 8px;
                font-size: 28px;
            }
            p {
                margin: 0;
                color: rgba(255, 255, 255, 0.85);
            }
        </style>
    </head>
    <body>
        <div class="card">
            <h1>Maintenance</h1>
            <p>{{ $message }}</p>
        </div>
    </body>
</html>
