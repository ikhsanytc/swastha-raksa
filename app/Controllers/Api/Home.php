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
                        'message' => 'Product dengan bukan milik anda!'
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
                    'message' => 'Produk tidak ada di database',
                ], 400);
            }
            if ($checkProduct['owner_uid'] !== $user->uid) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Product bukan milik anda!'
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
                    'message' => 'User tidak ditemukan',
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
            $products_request = $this->request->getVar('products');
            $uid_buyer = $this->request->getVar('uid_buyer');
            $uid = AuthUser::get()->uid;
            $userInfo = $this->usersModel->where('uid', $uid)->first();
            $userInfoPembeli = $this->usersModel->where('uid', $uid_buyer)->first();
            if (!$products_request || !$uid_buyer) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Products dan uid_buyer dibutuhkan'
                ], 400);
            }
            $products = json_decode($products_request, true);
            if (!is_array($products)) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Products bukan array'
                ], 400);
            }

            if ($userInfo['tipe_akun'] !== "Penjual") {
                return $this->respond([
                    'error' => true,
                    'message' => 'Kamu bukan penjual'
                ], 401);
            }
            if ($userInfo['uid'] == $uid_buyer) {
                return $this->respond([
                    'error' => true,
                    'message' => 'Tidak bisa melakukan transaksi ke akun yg sama'
                ], 400);
            }
            foreach ($products as $index => $product) {
                if (!isset($product['id_produk']) || !isset($product['jumlah_produk'])) {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Data ke-' . $index . ' tidak valid'
                    ], 400);
                }
                $product_database = $this->productsModel->find($product['id_produk']);
                if (!$product_database) {
                    return $this->respond([
                        'error' => true,
                        'message' => 'Produk tidak ditemukan'
                    ], 400);
                }
                $informasiOwner = $this->usersModel->where('uid', $product_database['owner_uid'])->first();
                if (!$informasiOwner) {
                    return $this->respond([
                        'error' => true,
                        'message' => "Informasi User pada data ke-$index tidak ada atau tidak ditemukan"
                    ], 400);
                }
                $total_harga = intval($product_database['harga_product']) * intval($product['jumlah_produk']);
                $product_data = [];
                if ($userInfoPembeli) {
                    $product_data = [
                        'id_produk' => $product['id_produk'],
                        'nama_penjual' => $informasiOwner['username'],
                        'nama_pembeli' => $userInfoPembeli['username'],
                        'jumlah_produk' => $product['jumlah_produk'],
                        'total_harga' => $total_harga,
                        'nama_produk' => $product_database['nama_product'],
                        'jenis_produk' => $product_database['jenis_product'],
                        'harga_produk' => $product_database['harga_product']
                    ];
                } else {
                    $product_data = [
                        'id_produk' => $product['id_produk'],
                        'nama_penjual' => $informasiOwner['username'],
                        'nama_pembeli' => 'tidak ada',
                        'jumlah_produk' => $product['jumlah_produk'],
                        'total_harga' => $total_harga,
                        'nama_produk' => $product_database['nama_product'],
                        'jenis_produk' => $product_database['jenis_product'],
                        'harga_produk' => $product_database['harga_product']
                    ];
                }
                if ($product_database['stok_product'] == 0) {
                    return $this->respond([
                        'error' => true,
                        'message' => "Produk {$product_database['nama_product']} telah habis stoknya"
                    ], 409);
                }
                $this->productsModel->save([
                    'product_id' => $product['id_produk'],
                    'stok_product' => $product_database['stok_product'] - $product['jumlah_produk']
                ]);
                $this->transactionModel->save([
                    'seller_uid' => $uid,
                    'buyer_uid' => $uid_buyer,
                    'transaction_time' => time(),
                    'product_data' => json_encode($product_data)
                ]);
            }



            // $data_produk_filter = array_filter($data_produk, fn($k) => $k !== 'owner_uid' && $k !== 'stok_product', ARRAY_FILTER_USE_KEY);
            // $this->transactionModel->save([
            //     'seller_uid' => $uid,
            //     'buyer_uid' => $uid_buyer,
            //     'transaction_time' => time(),
            //     'product_data' => json_encode($data_produk_filter)
            // ], 200);
            // $this->productsModel->save([
            //     'product_id' => $id_produk,
            //     'stok_product' => $data_produk['stok_product'] - 1
            // ]);
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
