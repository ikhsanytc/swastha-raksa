<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dokumentasi backend swastha raksa</title>
    <link rel="stylesheet" href="<?= base_url('/css/output.css') ?>">
    <script src="https://unpkg.com/feather-icons"></script>
</head>

<body>
    <nav class="fixed top-0 w-full bg-slate-100">
        <div class="h-14 px-14 flex justify-between items-center">
            <h1 class="font-bold text-2xl">Swastha Raksa</h1>
        </div>
    </nav>
    <?= $this->renderSection('content') ?>
    <script>
        feather.replace();
    </script>
</body>

</html>