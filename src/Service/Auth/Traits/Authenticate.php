<?php
namespace Levonliu\Packages\Service\Auth\Traits;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWT;
use Tymon\JWTAuth\JWTGuard;

trait Authenticate
{
    public function login(Request $request)
    {
        $this->validate($request, [
            $this->loginUsername() => 'required',
            'password'             => 'required',
        ]);
        $credentials = $this->getCredentials($request);
        return $this->attempt($request, $credentials);
    }

    protected function loginUsername()
    {
        return 'email';
    }

    protected function attempt(Request $request, $credentials)
    {
        $guard = $this->guard();
        if (!$guard->attempt($credentials)) {
            return $this->responseLoginFail();
        }

        $user = $guard->user();

        return $this->sendLoginResponse($user, $request);
    }

    protected function getCredentials(Request $request)
    {
        return $request->only([
            $this->loginUsername(),
            'password'
        ]);
    }

    protected function sendLoginResponse($user, Request $request)
    {
        return $this->respondWithToken($this->getJWTToken($request, $user));
    }

    protected function getJWTToken(Request $request, $user)
    {
        /** @var JWTGuard|JWT $guard */
        $guard = $this->guard();

        return $guard->fromUser($user);
    }

    protected function responseLoginFail()
    {
        return response()->json(['code' => 422, 'message' => '用户名或密码错误'], 422);
    }
    /**
     * @apiDefine ResponseToken
     * @apiSuccess (success) {String} access_token Access token
     * @apiSuccess (success) {Number} expires_in 过期时间(秒)
     * @apiSuccess (success) {String} type 类型(bearer token)
     * @apiSuccessExample {json} Token响应:
     *     {
     *       "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vYWRtaW4ua256LnRlc3QvYXBpL2FkbWluL3JlZnJlc2giLCJpYXQiOjE1MTc5ODY5MTksImV4cCI6MTUxODA1ODM3MywibmJmIjoxNTE4MDU0NzczLCJqdGkiOiJYV",
     *       "expires_in": 3600,
     *       "type" : "bearer"
     *     }
     */

    /**
     * @param $token
     * @param bool $withUser
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $withUser = false)
    {
        $data = [
            'access_token' => $token,
            'expires_in'   => $this->getTTL(),
            'type'         => 'bearer',
        ];

        if ($withUser) {
            $data['user'] = $this->user();
        }

        return response()->json($data);
    }

    /**
     * @return \Illuminate\Contracts\Auth\Guard|JWTGuard
     */
    protected function guard()
    {
        /** @var AuthManager $auth */
        $auth = app('auth');
        return $auth->guard();
    }

    public function logout()
    {
        $this->guard()->logout();
        return $this->afterLogout();
    }

    protected function afterLogout()
    {
        return null;
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    public function user()
    {
        return $this->guard()->user();
    }

    /**
     * @return int
     */
    protected function getTTL()
    {
        return $this->guard()->factory()->getTTL() * 1;
    }
}
