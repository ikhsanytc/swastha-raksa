<?php

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

$routes->options('(:any)', 'PreFlight::index'); // untuk menangani preflight

// untuk mengakses endpoint ini tidak perlu token
$routes->group('api/auth', function (RouteCollection $routes) {
    $routes->post('login', 'Api\Auth::login'); // untuk login
    $routes->post('register', 'Api\Auth::register'); // untuk register
});

$routes->get('/api/check_uid_ktp/(:any)', 'Api\Home::checkUidKtp/$1'); // untuk mengecek uid ktp ada apa gak di database, tidak memerlukan token untuk mengakses endpoint ini.

// API, perlu token untuk mengakses endpoint ini
$routes->group('api', ['filter' => 'auth'], function (RouteCollection $routes) {
    $routes->get('/', 'Api\Home::index'); // endpoint utama
    $routes->get('get_user_info', 'Api\Auth::getUserInfor'); // untuk mendapatkan informasi user
    $routes->delete('auth/logout', 'Api\Auth::logout'); // untuk logout
    $routes->post('auth/change_role', 'Api\Auth::changeRole'); // untuk ganti role akun
    $routes->post('edit_profile', 'Api\Auth::editProfile'); // untuk edit profile user
    $routes->post('edit_data_toko', 'Api\Auth::editDataToko'); // untuk edit data toko user
    $routes->post('add_product', 'Api\Home::addProduct'); // untuk menambahkan produk (hanya bisa dilakukan oleh user penjual)
    $routes->delete('delete_product/(:any)', 'Api\Home::deleteProduct/$1'); // untuk menghapus produk (hanya bisa dilakukan oleh user penjual)
    $routes->post('edit_product', 'Api\Home::editProduct'); // untuk edit produk (hanya bisa dilakukan oleh user penjual)
    $routes->get('get_product', 'Api\Home::getProduct'); // untuk mendapatkan data product (hanya bisa dilakukan oleh user penjual)
    $routes->get('get_transaction', 'Api\Home::getTransaction'); // untuk mendapatkan data transaction user
    $routes->post('add_transaction', 'Api\Home::addTransaction'); // untuk menambah riwayat transaction
});

$routes->set404Override('\App\Controllers\Home::notFound');
