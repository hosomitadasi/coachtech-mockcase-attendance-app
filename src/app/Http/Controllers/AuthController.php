<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use App\Actions\Fortify\CreateNewUser;


class AuthController extends Controller
{
    protected $creator;
    // creatorという変数を準備。会員登録の時に新しいユーザーを作る専用クラス。

    public function __construct(CreateNewUser $creator)
    {
        $this->creator = $creator;
        // AuthController の中でいつでも使えるように保存する場所。
    }

    public function adminLogin()
    {
        return view('admin/admin-login');
        // 画面を返す処理。管理者のログイン画面をユーザーに表示するという処理。
    }

    public function adminDoLogin(AdminLoginRequest $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            // Auth::attemptでログインできるかチェックする。
            // $request->only('email', 'password'はログインに必要な情報であるメールアドレスとパスワードだけ取り出す。
            $user = Auth::user();
            // ログイン成功した場合ユーザーの情報を取得。

            if ($user->admin_status) {
                return redirect('admin/attendance/list');
            } else {
                Auth::logout();
                return redirect()->back()->withErrors([
                    'email' => 'ログイン情報が登録されていません'
                ]);
            }
            // ユーザー情報の中にadmin_statusがあるかどうか確認。あるならtrue、ないならfalse。trueなら管理者画面へ、そうでない場合はログアウトしエラーを返す。
        }
        return redirect()->back()->withErrors([
        'email' => 'ログイン情報が登録されていません'
        ])->withInput();
        // 元のページに戻しつつ、エラー文を表示する。withInput()で入力していたメールアドレスを保持しておく。
    }

    public function adminLogout()
    {
        Auth::logout();
        return redirect('/admin/login');
    }
    // ログイン情報を消して管理者ログイン画面へ戻る。


    public function store(RegisterRequest $request)
    {
        $user = $this->creator->create($request->all());
        // $this->creator->createで新しいユーザーが作成される。$request->all()でUser.phpモデルのprotected $fillableに記載されているデータベースを登録する。
        $user->sendEmailVerificationNotification();
        // 認証リンクをメールで送る処理を実施する。
        return redirect('/register')->with('message', '登録が完了しました。認証メールを送信しましたのでご確認ください。');
    }

    public function doLogin(LoginRequest $request)
    {

        $credentials = $request->only('email', 'password');
        // 必要な情報（メール・パスワード）だけ取り出す。
        if (Auth::attempt($credentials)) {
            // もし成功したら$userにその情報が入る。
            $user = Auth::user();

            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                $this->sendVerificationEmail($user);
                return redirect()->back()->withErrors([
                'email' => 'メール認証が必要です。認証メールを再送信しました。'
                ]);
            }
            return redirect()->intended('/login');
        }

        return redirect()->back()->withErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
        // もしログインに失敗したらエラーを返す。
    }

    public function doLogout()
    {
        Auth::logout();
        return redirect('/login');
    }

    protected function sendVerificationEmail($user)
    {
        $user->sendEmailVerificationNotification();
    }
    // 認証メールの通知を送る機能。


}
