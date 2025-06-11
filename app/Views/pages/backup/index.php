<?= $this->extend('layout/template') ?>
<?= $this->section('content') ?>

<div class="px-4 min-h-screen flex justify-center items-center">
    <div class="fixed flex justify-center items-center w-full h-full bg-gray-800/10 z-50 transition-opacity duration-300 opacity-0 pointer-events-none" id="modalDelete">
        <div class="lg:w-1/2 w-full bg-white rounded p-4">
            <div class="flex justify-between items-center">
                <h1 class="font-bold text-2xl">Delete all files</h1>
                <button type="button" onclick="closeModalDelete()" class="cursor-pointer">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="mt-4">
                <p>Kamu yakin ingin menghapus semua file di folder uploads?</p>
            </div>
            <form action="<?= base_url('/backup/delete') ?>" method="post" class="mt-4 flex justify-end gap-4">
                <input type="hidden" name="_method" value="DELETE">
                <button class="bg-red-600 px-7 py-1 cursor-pointer hover:scale-105 active:scale-95 transition duration-150 text-white rounded font-semibold" type="submit">Ya, saya ingin</button>
                <button class="bg-gray-600 px-7 py-1 cursor-pointer hover:scale-105 active:scale-95 transition duration-150 text-white rounded font-semibold" type="button" onclick="closeModalDelete()">Ga jadi</button>
            </form>
        </div>
    </div>
    <div class="flex flex-col items-center text-center">
        <?php if (session()->getFlashdata('success')) : ?>
            <div class="mb-5 bg-green-300 rounded w-full h-14 text-center flex justify-center items-center">
                <h1 class="text-green-600"><?= session()->getFlashdata('success') ?></h1>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')) : ?>
            <div class="mb-5 bg-red-300 rounded w-full h-14 text-center flex justify-center items-center">
                <h1 class="text-red-600"><?= session()->getFlashdata('error') ?></h1>
            </div>
        <?php endif; ?>
        <h1 class="text-4xl font-bold mb-5">Backup Uploads Folder</h1>
        <a href="<?= base_url('/backup/download') ?>" target="_blank" class="bg-blue-600 px-4 py-2 text-white rounded-lg font-semibold cursor-pointer hover:scale-105 transition duration-150 active:scale-95 w-full">Download upload files</a>
        <form action="<?= base_url('/backup/upload') ?>" method="post" class="mt-5 w-full" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <button type="button" id="triggerZipUpload" class="bg-blue-600 px-4 py-2 text-white rounded-lg font-semibold cursor-pointer hover:scale-105 transition duration-150 active:scale-95 w-full">
                Store upload files
            </button>
            <input type="file" class="hidden" accept=".zip" id="zip_file" name="zip_file" onchange="this.form.submit()">
        </form>
        <div class="w-full mt-5">
            <button type="button" onclick="openModalDelete()" class="bg-red-600 px-4 py-2 text-white rounded-lg font-semibold cursor-pointer hover:scale-105 w-full transition duration-150 active:scale-95">Delete all files in uploads folder</button>
        </div>
    </div>
</div>
<script>
    document.getElementById("triggerZipUpload").addEventListener('click', function() {
        document.getElementById("zip_file").click();
    })

    function openModalDelete() {
        const modal = document.getElementById("modalDelete");
        modal.classList.remove("pointer-events-none");
        modal.classList.remove("opacity-0");
        modal.classList.add("opacity-100");
    }

    function closeModalDelete() {
        const modal = document.getElementById("modalDelete");
        modal.classList.remove("opacity-100");
        modal.classList.add("opacity-0")
        modal.classList.add("pointer-events-none");
    }
</script>
<?= $this->endSection() ?>