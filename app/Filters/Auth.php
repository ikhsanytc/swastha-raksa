<?php

namespace App\Filters;

use App\Models\BlacklistKeyModel;
use App\Models\UsersModel;
use App\Services\AuthUser;
use App\Services\TokenUser;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Auth implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth_key');
        $authHeader = $request->getHeaderLine('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->setStatusCode(401)->setJSON([
                'error' => true,
                'message' => 'Butuh token key'
            ]);
        }

        $token = substr($authHeader, 7);
        $decoded = validateAuthKey($token);
        if (!$decoded) {
            return response()->setStatusCode(401)->setJSON([
                'error' => true,
                'message' => 'Invalid or expired token'
            ]);
        }
        $blacklistModel = new BlacklistKeyModel();
        $blacklist = $blacklistModel->where('key', $token)->first();
        if ($blacklist) {
            return response()->setStatusCode(401)->setJSON([
                'error' => true,
                'message' => 'Anda sudah logout dari sesi ini'
            ]);
        }
        $usersModel = new UsersModel();
        $user_check = $usersModel->where('uid', $decoded->uid)->first();
        if (!$user_check) {
            $blacklistModel->save([
                'key' => $token,
            ]);
            return response()->setStatusCode(401)->setJSON([
                'error' => true,
                'message' => 'Invalid or expired token'
            ]);
        }
        AuthUser::set($decoded);
        TokenUser::set($token);
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
