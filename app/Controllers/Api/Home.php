<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ProductsModel;
use App\Models\TransactionModel;
use App\Models\UsersModel;
use App\Services\AuthUser;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class Home extends BaseController
{
    use ResponseTrait;
    protected $usersModel;
    protected $productsModel;
    protected $transactionModel;
    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->productsModel = new ProductsModel();
        $this->transactionModel = new TransactionModel();
    }
    public function index()
    {
        return $this->respond([
            'error' => false,
            'message' => 'Ini endpoint utama, ga guna',
        ]);
    }
    /**
     * Fungsinya untuk ngecek uid ktp pengguna ada atau ga di database.
     */
    public function checkUidKtp($uid)
    {
        try {
            $user = $this->usersModel->where('uid', $uid)->first();
            if (!$user) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Tidak ada akun dengan uid ktp tersebut'
                ], 401);
            }
            return $this->respond([
                'error' => false,
                'message' => 'Akun tersebut ada'
            ], 200);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Fungsinya untuk menambahkan product baru ke database.
     */
    public function addProduct()
    {
        try {
            $nama = $this->request->getVar('nama_produk');
            $jenis = $this->request->getVar('jenis_produk');
            $harga = $this->request->getVar('harga_produk');
            $stok = $this->request->getVar('stok_produk');
            $userInfo = AuthUser::get();
            $user = $this->usersModel->where('uid', $userInfo->uid)->first();
            if (!$user) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Akun tidak ditemukan'
                ], 500);
            }
            if (!$nama || !$jenis || !$harga || !$stok) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Nama, jenis, harga, dan stok dibutuhkan'
                ], 400);
            }
            if ($user['tipe_akun'] === 'Pembeli') {
                return $this->respond([
                    'error' => true,
                    'message' => 'Akun ini bukan penjual'
                ], 401);
            }
            $this->productsModel->save([
                'owner_uid' => $userInfo->uid,
                'nama_product' => $nama,
                'jenis_product' => $jenis,
                'harga_product' => $harga,
                'stok_product' => $stok,
            ]);
            return $this->respond([
                'error' => false,
                'message' => 'OK',
            ], 200);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Fungsinya untuk delete product dengan id tertera.
     */
    public function deleteProduct($id)
    {
        try {

            if (!$id) {
                return $this->respond([
                    'error' => true,
                    'message' => 'id dibutuhkan'
                ], 400);
            }
            $user = AuthUser::get();
            $checkUser = $this->usersModel->where('uid', $user->uid)->first();
            if ($checkUser['tipe_akun'] !== "Penjual") {
                return $this->respond([
                    'error' => true,
                    'message' => 'Anda bukan penjual'
                ], 401);
            }
            $checkProduct = $this->productsModel->find($id);
            if ($checkProduct) {
                if ($checkProduct['owner_uid'] !== $user->uid) {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Product dengan id = ' . $id . ' bukan milik anda!'
                    ], 400);
                }
                $this->productsModel->delete($id);
                return $this->respond([
                    'error' => false,
                    'message' => 'Berhasil delete'
                ], 200);
            }
            return $this->respond([
                'error' => true,
                'message' => 'Produk tidak ada di database'
            ], 500);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Fungsinya untuk edit product dengan id tertera
     */
    public function editProduct()
    {
        try {
            $harga = $this->request->getVar('harga_produk');
            $stok = $this->request->getVar('stok_produk');
            $id = $this->request->getVar('id_produk');
            $user = AuthUser::get();
            if (!$id) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Id diperlukan'
                ], 400);
            }
            $checkUser = $this->usersModel->where('uid', $user->uid)->first();
            if ($checkUser['tipe_akun'] !== 'Penjual') {
                return $this->respond([
                    'error' => true,
                    'message' => 'Anda bukan penjual'
                ], 401);
            }
            $checkProduct = $this->productsModel->find($id);
            if (!$checkProduct) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Tidak ada produk dengan id = ' . $id,
                ], 400);
            }
            if ($checkProduct['owner_uid'] !== $user->uid) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Product dengan id = ' . $id . ' bukan milik anda!'
                ], 400);
            }

            $data = [];
            if ($harga) {
                $data['harga_product'] = $harga;
            }
            if ($stok) {
                $data['stok_product'] = $stok;
            }
            if (!empty($data)) {
                $data['product_id'] = $id;
                $this->productsModel->save($data);
                $filter = array_filter($data, fn($k) => $k !== 'product_id', ARRAY_FILTER_USE_KEY);
                return $this->respond([
                    'error' => false,
                    'message' => 'Berhasil edit produk',
                    'data' => $filter,
                ], 200);
            }
            return $this->respond([
                'error' => false,
                'message' => 'Tidak ada yang berubah'
            ], 200);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getProduct()
    {
        try {

            $user = AuthUser::get();
            $userInfo = $this->usersModel->where('uid', $user->uid)->first();
            if (!$userInfo) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Tidak ada user dengan uid = ' . $user->uid,
                ], 401);
            }
            if ($userInfo['tipe_akun'] !== 'Penjual') {
                return $this->respond([
                    'error' => true,
                    'message' => 'Anda bukan penjual'
                ], 400);
            }
            $product = $this->productsModel->where('owner_uid', $user->uid)->findAll();
            if (empty($product)) {
                return $this->respond([
                    'error' => false,
                    'message' => 'Tidak ada product di akun anda'
                ], 200);
            }
            return $this->respond([
                'error' => false,
                'message' => 'OK',
                'data' => $product
            ], 200);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function getTransaction()
    {
        try {

            $user = AuthUser::get();
            $transactions = $this->transactionModel->findAll();
            $data = [];
            if (empty($transactions)) {
                return $this->respond([
                    'error' => false,
                    'message' => 'Tidak ada data',
                    'data' => []
                ], 200);
            }
            foreach ($transactions as $transaction) {
                if ($transaction['buyer_uid'] === $user->uid) {
                    $data[] = $transaction;
                } else if ($transaction['seller_uid'] === $user->uid) {
                    $data[] = $transaction;
                }
            }
            return $this->respond([
                'error' => false,
                'message' => "OK",
                'data' => $data,
            ], 200);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function addTransaction()
    {
        try {
            $uid_buyer = $this->request->getVar('uid_buyer');
            $id_produk = $this->request->getVar('id_produk');
            $uid = AuthUser::get()->uid;
            $userInfo = $this->usersModel->where('uid', $uid)->first();
            $data_produk = $this->productsModel->find($id_produk);
            if (!$uid_buyer || !$id_produk) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Uid buyer, dan id produk dibutuhkan'
                ], 400);
            }
            if (!$data_produk) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Produk tidak ada dengan id = ' . $id_produk
                ], 400);
            }
            if ($data_produk['owner_uid'] !== $uid) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Produk dengan id = ' . $id_produk . ' bukan punya anda'
                ], 401);
            }
            if ($userInfo['tipe_akun'] !== "Penjual") {
                return $this->respond([
                    'error' => true,
                    'message' => 'Kamu bukan penjual'
                ], 401);
            }
            $data_produk_filter = array_filter($data_produk, fn($k) => $k !== 'owner_uid' && $k !== 'stok_product', ARRAY_FILTER_USE_KEY);
            $this->transactionModel->save([
                'seller_uid' => $uid,
                'buyer_uid' => $uid_buyer,
                'transaction_time' => time(),
                'product_data' => json_encode($data_produk_filter)
            ], 200);
            $this->productsModel->save([
                'product_id' => $id_produk,
                'stok_product' => $data_produk['stok_product'] - 1
            ]);
            return $this->respond([
                'error' => false,
                'message' => 'Berhasil'
            ], 201);
        } catch (Exception $e) {
            return $this->respond([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
