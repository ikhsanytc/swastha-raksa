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
            if ($password) {
                if (preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $password)) {
                    $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                } else {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Password minimal 8 karakter, mengandung satu huruf kapital, mengandung angka, dan mengandung simbol'
                    ], 400);
                }
            }
            if ($email) {
                $data['email'] = $email;
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
