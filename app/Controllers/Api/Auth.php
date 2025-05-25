<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\BlacklistKeyModel;
use App\Models\ProductsModel;
use App\Models\UsersModel;
use App\Services\AuthUser;
use App\Services\TokenUser;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class Auth extends BaseController
{
    use ResponseTrait;
    protected $helpers = ['auth_key'];
    protected $usersModel;
    protected $productsModel;
    protected $blacklistModel;
    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->blacklistModel = new BlacklistKeyModel();
        $this->productsModel = new ProductsModel();
    }

    /**
     * Fungsinya untuk ganti role akun ke pembeli atau penjual.
     */
    public function changeRole()
    {
        try {
            $role = $this->request->getVar('role');
            $userUid = AuthUser::get()->uid;
            $user = $this->usersModel->where('uid', $userUid)->first();
            if (!$role) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Role dibutuhkan'
                ], 400);
            }
            if ($role !== 'Pembeli' && $role !== 'Penjual') {
                return $this->respond([
                    'error' => true,
                    'message' => "Tidak ada role dengan nama $role, hanya tersedia Pembeli dan Penjual"
                ], 400);
            }
            if ($user['tipe_akun'] === $role) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Anda sudah menjadi ' . $role
                ], 400);
            }
            if ($user['tipe_akun'] === 'Penjual' && $role === 'Pembeli') {
                $this->productsModel->where('owner_uid', $userUid)->delete();
                $this->usersModel->save([
                    'uid' => $userUid,
                    'tipe_akun' => 'Pembeli',
                    'data_toko' => null,
                ]);
                return $this->respond([
                    'error' => false,
                    'message' => 'Berhasil rubah akun ke tipe pembeli, semua produk yang mengenai akun ini dihapus'
                ], 200);
            }
            $this->usersModel->save([
                'uid' => $userUid,
                'tipe_akun' => 'Penjual'
            ]);
            return $this->respond([
                'error' => false,
                'message' => 'Berhasi rubah akun ke tipe penjual, kini anda bisa menjual produk'
            ]);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Fungsinya untuk edit profile pengguna.
     */
    public function editProfile()
    {
        try {

            $userInfo = AuthUser::get();
            $username = $this->request->getVar('username');
            $password = $this->request->getVar('password');
            $password_old = $this->request->getVar('password_old');
            $email = $this->request->getVar('email');
            $data_toko = $this->request->getVar('data_toko');
            $profile_picture = $this->request->getFile('profile_picture');
            $data_lama_user = $this->usersModel->where('uid', $userInfo->uid)->first();
            if (!$data_lama_user) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Akun anda tidak ditemukan'
                ], 401);
            }
            $data = [];
            if ($username) {
                $data['username'] = $username;
            }
            if ($data_toko) {
                if ($data_lama_user['tipe_akun'] === "Penjual") {
                    $data['data_toko'] = $data_toko;
                } else {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Akun anda bukan penjual'
                    ], 400);
                }
            }
            if ($password && $password_old) {
                if (preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $password)) {

                    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

                    if (password_verify($password_old, $data_lama_user['password'])) {
                        $data['password'] = $password_hashed;
                    } else {
                        return $this->respond([
                            'error' => true,
                            'message' => 'Password salah'
                        ], 400);
                    }
                } else {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Password minimal 8 karakter, mengandung satu huruf kapital, mengandung angka, dan mengandung simbol'
                    ], 400);
                }
            }
            if ($email) {

                // Ini kodenya gw ubah
                // jadi dia ngecek dulu ini emailnya valid atau gk
                // kalau gk nanti dia error

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $data['email'] = $email;
                } else {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Email tidak valid'
                    ], 400);
                }
            }
            if ($profile_picture && $profile_picture->isValid()) {

                // Ini kodenya juga gw ubah
                // karena Godot tidak mendukung upload jenis JPG/PNG
                // jadi gw ubah dulu datanya ke bentuk teks yang nanti bakal di parse lagi di client

                if (!in_array($profile_picture->getClientExtension(), ['jpg', 'jpeg', 'png', 'txt'])) {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Hanya ekstensi jpg, jpeg, png yang diperbolehkan'
                    ], 415);
                }
                if ($profile_picture->getSizeByBinaryUnit(\CodeIgniter\Files\FileSizeUnit::MB) > 10) {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Maksimal 10 MB size gambarnya'
                    ], 413);
                }

                $profile_picture_actual_type = explode(".", $profile_picture->getName());
                $profile_picture_actual_type = $profile_picture_actual_type[count($profile_picture_actual_type) - 2];
                $profile_name = $profile_picture->getRandomName();
                $profile_name = $profile_picture_actual_type . "." . $profile_name;

                $profile_picture->move(FCPATH . 'uploads', $profile_name);

                if ($data_lama_user['profile_picture'] !== 'nophoto.jpg') {
                    unlink(FCPATH . 'uploads/' . $data_lama_user['profile_picture']);
                }
                $data['profile_picture'] = $profile_name;
            }
            if (!empty($data)) {
                $data['uid'] = $userInfo->uid;
                $this->usersModel->save($data);
                if (isset($data['profile_picture'])) {
                    $data['profile_picture'] = base_url("/uploads/{$data['profile_picture']}");
                }
                $filter = array_filter($data, fn($k) => $k !== 'password' && $k !== 'uid', ARRAY_FILTER_USE_KEY);
                return $this->respond([
                    'error' => false,
                    'message' => 'Berhasil',
                    'data' => $filter,
                ], 201);
            }
            return $this->respond([
                'error' => false,
                'message' => 'Tidak ada yg diubah'
            ], 200);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Fungsinya untuk mengedit data toko pengguna
     */
    public function editDataToko()
    {
        try {

            $userInfo = AuthUser::get();
            $alamat = $this->request->getVar("alamat");
            $nama = $this->request->getVar("nama");
            $jenis = $this->request->getVar("jenis");
            $nib = $this->request->getVar("nib");
            $surat_izin_perdagangan = $this->request->getFile('surat_izin_perdagangan');
            $scan_ktp = $this->request->getFile('scan_ktp');
            $selfie_ktp = $this->request->getFile('selfie_ktp');
            $data_lama_user = $this->usersModel->where('uid', $userInfo->uid)->first();

            if (!$data_lama_user) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Akun anda tidak ditemukan'
                ], 401);
            }

            // Upload dokumen dokumen penting
            $tempData = [];

            // Validasi input file
            $validationRule = [
                'surat_izin_perdagangan' => [
                    'label' => 'File',
                    'rules' => [
                        'uploaded[surat_izin_perdagangan]',
                        'mime_in[surat_izin_perdagangan,text/plain]',
                        'max_size[surat_izin_perdagangan,10000]',
                    ],
                ],
                'scan_ktp' => [
                    'label' => 'File',
                    'rules' => [
                        'uploaded[scan_ktp]',
                        'mime_in[scan_ktp,text/plain]',
                        'max_size[scan_ktp,10000]',
                    ],
                ],
                'selfie_ktp' => [
                    'label' => 'File',
                    'rules' => [
                        'uploaded[selfie_ktp]',
                        'mime_in[selfie_ktp,text/plain]',
                        'max_size[selfie_ktp,10000]',
                    ],
                ],
            ];

            if (! $this->validateData([], $validationRule)) {
                return $this->respond([
                    'error' => true,
                    'message' => 'File yang di upload tidak valid'
                ], 400);
            }

            // simpan file di public/uploads/

            // di CI4 ada error yg gw gk ngerti
            // kalau pake $file->store() ntar muncul
            // mkdir(): No such file or directory
            // sumpah gk ngerti gw kenapa begitu gk jelas
            // makanya gw pake ini aja
            // $file_extension = explode(".", $_FILES["surat_izin_perdagangan"]["name"]);
            // $file_extension = $file_extension[count($file_extension) - 2];
            // $file_path = $file_extension . "." . $userInfo->uid . "_surat_izin_perdagangan.txt";
            // move_uploaded_file($_FILES["surat_izin_perdagangan"]["tmp_name"], FCPATH . "uploads/" . $file_path);
            // $tempData["surat_izin_perdagangan"] = $file_path;

            // Seharusnya pake cara ini dit, sesuai dokumentasi ci4.
            if ($surat_izin_perdagangan->isValid()) {
                $file_name = $surat_izin_perdagangan->getName();
                $parts = explode(".", $file_name);
                $file_extension = $parts[count($parts) - 2];
                $filename = $file_extension . "." . $userInfo->uid . "_surat_izin_perdagangan.txt";
                if (!$surat_izin_perdagangan->hasMoved()) {
                    $surat_izin_perdagangan->move(FCPATH . 'uploads', $filename);
                }
                $tempData["surat_izin_perdagangan"] = $filename;
            } else {
                return $this->respond([
                    'error' => true,
                    'message' => "surat izin perdagangan tidak valid"
                ], 400);
            }

            // $file_extension = explode(".", $_FILES["scan_ktp"]["name"]);
            // $file_extension = $file_extension[count($file_extension) - 2];
            // $file_path = $file_extension . "." . $userInfo->uid . "_scan_ktp.txt";
            // move_uploaded_file($_FILES["scan_ktp"]["tmp_name"], FCPATH . "uploads/" . $file_path);
            // $tempData["scan_ktp"] = $file_path;

            if ($scan_ktp->isValid()) {
                $file_name = $surat_izin_perdagangan->getName();
                $parts = explode(".", $file_name);
                $file_extension = $parts[count($parts) - 2];
                $filename = $file_extension . "." . $userInfo->uid . "_scan_ktp.txt";
                if (!$scan_ktp->hasMoved()) {
                    $scan_ktp->move(FCPATH . 'uploads', $filename);
                }
                $tempData['scan_ktp'] = $filename;
            } else {
                return $this->respond([
                    'error' => true,
                    'message' => "scan ktp tidak valid"
                ], 400);
            }

            // $file_extension = explode(".", $_FILES["selfie_ktp"]["name"]);
            // $file_extension = $file_extension[count($file_extension) - 2];
            // $file_path = $file_extension . "." . $userInfo->uid . "_selfie_ktp.txt";
            // move_uploaded_file($_FILES["selfie_ktp"]["tmp_name"], FCPATH . "uploads/" . $file_path);
            // $tempData["selfie_ktp"] = $file_path;

            if ($selfie_ktp->isValid()) {
                $file_name = $surat_izin_perdagangan->getName();
                $parts = explode(".", $file_name);
                $file_extension = $parts[count($parts) - 2];
                $filename = $file_extension . "." . $userInfo->uid . "_selfie_ktp.txt";
                if (!$selfie_ktp->hasMoved()) {
                    $selfie_ktp->move(FCPATH . 'uploads', $filename);
                }
                $tempData['selfie_ktp'] = $filename;
            } else {
                return $this->respond([
                    'error' => true,
                    'message' => "selfie ktp tidak valid"
                ], 400);
            }

            // Update data toko
            $data = [];
            $data_lama_user['data_toko'] = json_decode($data_lama_user['data_toko'], true);

            if ($data_lama_user['tipe_akun'] === "Penjual") {
                $data_lama_user['data_toko']['alamat'] = $alamat;
                $data_lama_user['data_toko']['nama'] = $nama;
                $data_lama_user['data_toko']['jenis'] = $jenis;
                $data_lama_user['data_toko']['nib'] = $nib;
                foreach ($tempData as $key => $value) {
                    $data_lama_user['data_toko'][$key] = $value;
                }
                $data['data_toko'] = json_encode($data_lama_user['data_toko']);
            } else {
                return $this->respond([
                    'error' => true,
                    'message' => 'Akun anda bukan penjual'
                ], 400);
            }

            if (!empty($data)) {

                $data['uid'] = $userInfo->uid;
                $this->usersModel->save($data);

                $filter = array_filter($data, fn($k) => $k !== 'password' && $k !== 'uid', ARRAY_FILTER_USE_KEY);
                return $this->respond([
                    'error' => false,
                    'message' => 'Berhasil',
                    'data' => $filter,
                ], 201);
            }

            return $this->respond([
                'error' => false,
                'message' => 'Tidak ada yg diubah'
            ], 200);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Fungsinya untuk login.
     */
    public function login()
    {
        try {

            $username = $this->request->getVar('username');
            $password = $this->request->getVar('password');
            $uid_ktp = $this->request->getVar('uid_ktp');
            if (!$username || !$password || !$uid_ktp) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Username, password, dan uid ktp dibutuhkan'
                ], 400);
            }
            $user = $this->usersModel->where('uid', $uid_ktp)->where('username', $username)->first();
            if (!$user) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Username atau password salah'
                ], 401);
            }
            if (!password_verify($password, $user['password'])) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Username atau password salah'
                ], 401);
            }
            $authKey = createAuthKey($user['uid']);
            return $this->respond([
                'error' => false,
                'message' => 'OK',
                'data' => [
                    'auth_key' => $authKey,
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'nik' => $user['nik'],
                    'profile_picture' => base_url("/uploads/{$user['profile_picture']}"),
                    'tipe_akun' => $user['tipe_akun'],
                    'data_toko' => $user['data_toko'],
                ]
            ]);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Fungsinya untuk register
     */
    public function register()
    {
        try {
            $uid_ktp = $this->request->getVar('uid_ktp');
            $username = $this->request->getVar('username');
            $password = $this->request->getVar('password');
            $tipe_akun = $this->request->getVar('tipe_akun');
            $nik = $this->request->getVar('nik');
            $profile_picture = $this->request->getFile('profile_picture');
            $user_check_username = $this->usersModel->where('username', $username)->first();
            $user_check_uid_ktp = $this->usersModel->where('uid', $uid_ktp)->first();
            $profile_name = '';
            if (!$uid_ktp || !$username || !$password) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Uid ktp, username, dan password dibutuhkan',
                ], 400);
            }
            if ($user_check_username || $user_check_uid_ktp) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Akun sudah ada'
                ], 409);
            }
            if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $password)) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Password minimal 8 karakter, mengandung satu huruf kapital, mengandung angka, dan mengandung simbol'
                ], 400);
            }
            if ($profile_picture && $profile_picture->isValid()) {
                if (!in_array($profile_picture->getClientExtension(), ['jpg', 'jpeg', 'png'])) {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Hanya ekstensi jpg, jpeg, png yang diperbolehkan'
                    ], 415);
                }
                if ($profile_picture->getSizeByBinaryUnit(\CodeIgniter\Files\FileSizeUnit::MB) > 2) {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Maksimal 2 MB size gambarnya'
                    ], 413);
                }
                $profile_name = $profile_picture->getRandomName();
                $profile_picture->move(FCPATH . 'uploads', $profile_name);
            }
            $nameProfile = $profile_name === '' ? 'nophoto.jpg' : $profile_name;
            $this->usersModel->insert([
                'uid' => $uid_ktp,
                'username' => $username,
                'nik' => $nik ?? null,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'profile_picture' => $nameProfile,
                'tipe_akun' => $tipe_akun ?? 'Pembeli'
            ]);
            $auth_key = createAuthKey($uid_ktp);
            return $this->respond([
                'error' => false,
                'message' => 'Success',
                'data' => [
                    'auth_key' => $auth_key,
                    'username' => $username,
                    'nik' => $nik ?? '',
                    'email' => '',
                    'profile_picture' => base_url("/uploads/$nameProfile"),
                    'tipe_akun' => $tipe_akun ?? 'Pembeli',
                    'data_toko' => []
                ]
            ], 201);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Fungsinya untuk logout dan blacklist token yang dimiliki user.
     */
    public function logout()
    {
        try {
            $key = TokenUser::get();
            $this->blacklistModel->save([
                'key' => $key,
            ]);
            return $this->respond([
                'error' => false,
                'message' => 'success',
            ], 200);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Fungsinya untuk mendapatkan informasi user dari database
     */
    public function getUserInfor()
    {
        try {
            $userInfor = AuthUser::get();
            $userDatabase = $this->usersModel->where('uid', $userInfor->uid)->first();
            $filter = array_filter($userDatabase, fn($k) => $k !== 'password', ARRAY_FILTER_USE_KEY);
            if ($filter['profile_picture']) {
                $filter['profile_picture'] = base_url("/uploads/{$filter['profile_picture']}");
            }
            return $this->respond([
                'error' => false,
                'message' => 'OK',
                'data' => $filter,
            ]);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
