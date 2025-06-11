<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>
<div class="px-14 pt-20">
    <h1 class="text-5xl mb-3 font-bold">Halo,</h1>
    <h4 class="text-3xl font-semibold mb-2">Kunjungi <a href="https://dokumentasi-swastha-raksa.vercel.app/" class="text-blue-600 hover:underline">dokumentasi-swastha-raksa.vercel.app</a> untuk dokumentasi.</h4>
    <div class="flex flex-col gap-2 mt-5">
        <h4 class="font-semibold text-xl">Status JWT_SECRET = <?= $env_check ?></h4>
        <h4 class="font-semibold text-xl">Mode = <?= ENVIRONMENT ?></h4>
        <h4 class="font-semibold text-xl">Database type = <?= ENVIRONMENT === "production" ? "mysql" : "sqlite" ?></h4>
        <h4 class="font-semibold text-xl">Base url = <a href="<?= base_url() ?>" class="text-blue-600 hover:underline"><?= base_url() ?></a></h4>
    </div>
</div>
<?= $this->endSection() ?>