<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use SebastianBergmann\CodeUnitReverseLookup\Wizard;
use ZipArchive;

class Backup extends BaseController
{
    public function index()
    {
        return view('pages/backup/index');
    }

    public function uploadZip()
    {
        $file = $this->request->getFile('zip_file');

        if (!$file->isValid() || $file->getClientExtension() !== 'zip') {
            return redirect()->to(base_url('/backup'))->with('error', 'File harus berupa .zip');
        }
        $tempPath = WRITEPATH . 'uploads_temp/';
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
        $zipPath = $tempPath . $file->getRandomName();
        $file->move($tempPath, basename($zipPath));

        $extractPath = FCPATH . 'uploads/';
        if (!is_dir($extractPath)) {
            mkdir($extractPath, 0755, true);
        }
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            unlink($zipPath);
            return redirect()->to(base_url('/backup'))->with('success', 'File berhasil di ekstrak!');
        } else {
            return redirect()->to(base_url('/backup'))->with('error', 'Gagal membuka zip file');
        }
    }

    public function deleteUploads()
    {
        $uploadsPath = FCPATH . 'uploads';
        if (!is_dir($uploadsPath)) {
            mkdir($uploadsPath, 0755, true);
        }
        $files = glob($uploadsPath . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return redirect()->to(base_url('/backup'))->with('success', 'Berhasil menghapus semua file');
    }

    public function downloadUploadsZip()
    {
        $uploadsPath = FCPATH . 'uploads';
        $zipName = "uploads_backup.zip";
        $zipPath = WRITEPATH . $zipName;

        // Pastikan folder ada
        if (!is_dir($uploadsPath)) {
            return $this->response->setStatusCode(404)->setBody('Folder uploads tidak ditemukan.');
        }

        // Hapus file zip lama jika ada
        if (file_exists($zipPath)) {
            unlink($zipPath);
        }

        $files = glob($uploadsPath . '/*');
        if (empty($files)) {
            return redirect()->to(base_url('/backup'))->with('error', 'Folder uploads kosong.');
        }

        // Buat file ZIP
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return $this->response->setStatusCode(500)->setBody('Gagal membuat file ZIP.');
        }

        $this->addFilesToZip($zip, $uploadsPath, '');
        $zip->close();

        // Kirim file sebagai unduhan
        return $this->response->setJSON([
            'error' => false,
            'message' => 'OK',
        ])->download($zipPath, null)->setFileName($zipName);
    }


    private function addFilesToZip(ZipArchive $zip, string $folderPath, string $relativePath)
    {
        $files = scandir($folderPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $fullPath = $folderPath . DIRECTORY_SEPARATOR . $file;
            $localPath = $relativePath . $file;

            if (is_dir($fullPath)) {
                $zip->addEmptyDir($localPath);
                $this->addFilesToZip($zip, $fullPath, $localPath . '/');
            } else {
                $zip->addFile($fullPath, $localPath);
            }
        }
    }
}
