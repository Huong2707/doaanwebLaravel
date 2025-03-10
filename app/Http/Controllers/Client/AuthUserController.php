<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthUserController extends Controller
{
    public function register()
    {
        return view('client/register');
    }

    public function post_register(Request $request)
    {
        // $this->validate($request, [
        //     'username' => 'required|string',
        //     'phone' => 'required|numeric',
        //     'password' => 'required|min:6',
        // ]);
        $validator = \Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'numeric'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'username.required' => 'Tên đăng nhập là bắt buộc.',
            'username.string' => 'Tên đăng nhập phải là chuỗi ký tự.',
            'username.max' => 'Tên đăng nhập không được vượt quá 255 ký tự.',
            
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'phone.numeric' => 'Số điện thoại phải là số.',
            
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);
        
    
        if ($validator->fails()) {
            $errors = $validator->errors();
            
            if (!$request->username && !$request->phone && !$request->password 
            || !$request->username && $request->phone && !$request->password
            || !$request->username && !$request->phone && $request->password) {
                $errors = ['username' => ['Tên đăng nhập là bắt buộc.']];
            }
    
            return back()->withErrors($errors);
        }

        if (User::where('username', $request->username)->exists()) {
            return back()->with('error', 'Username đã tồn tại');
        }

        $user = new User();
        $user->username = $request->username;
        $user->phone = $request->phone;
        $user->password = bcrypt($request->password);

        try {
            $user->save();
            return back()->with('success', 'Đăng ký thành công');
        } catch (\Exception $e) {
            \Log::info($e);
            return back()->with('error', 'Đăng ký thất bại');
        }

    }

    public function login(Request $request)
    {
        $redirect_uri = $request->redirect_uri;
        return view('client/login', compact('redirect_uri'));
    }

    public function post_login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required|min:6',
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        if (!Auth::guard('web')->attempt([
            'username' => $request->username,
            'password' => $request->password,
            'role' => 3
        ])) {
            return redirect()->back()->with('error', 'Tài khoản hoặc mật khẩu không chính xác');
        }
        \Log::info($request->redirect_uri);
        return redirect()->to($request->redirect_uri)->with('sucess', 'Login success!');
    }

    public function logout()
    {
        Auth::logout();
        return back();
    }

    public function forgot_password()
    {
        return view('client.forgot-password');
    }

    public function post_forgot_password(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $checkUser = User::where('email', $email)->first();

        if (!$checkUser) {
            return back()->with('error', 'Địa chỉ email không tồn tại!');
        }

        $code = md5(time() . $email);
        $checkUser->code = $code;
        $checkUser->time_code = Carbon::now();
        $checkUser->save();

        $redirect_uri = route('reset-user-password', ['code' => $checkUser->code, 'email' => $email]);
        $this->sendEmail($checkUser, $redirect_uri);

        return back()->with('success', 'Link lấy lại mật khẩu đã gửi vào email của bạn!');
    }

    public function reset_password(Request $request)
    {
        $email = $request->email;
        $code = $request->code;
        $checkUser = User::where(['email' => $email, 'code' => $code])->first();

        if (!$checkUser) {
            return redirect()->route('forgot-user-password')->with('error', 'Đường dẫn tạo lại mật khẩu không đúng, vui lòng thử lại sau!');
        }

        return view('client.reset-password', compact('email', 'code'));
    }
}
