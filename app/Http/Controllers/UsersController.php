<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{
    public function __construct() {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);
    }

    public function create() {
    	return view('users.create');
    }

    public function show(User $user) {
        $statuses = $user->statuses()->orderBy('created_at', 'desc')->paginate(30); //获取微博数据
        return view('users.show', compact('user', 'statuses'));
    }

    public function store(Request $request) {
    	$this->validate($request, [
    		'name' => 'required|max:50',
    		'email' => 'required|email|unique:users|max:255',
    		'password' => 'required',
    	]);

    	$user = User::create([
    		'name' => $request->name,
    		'email' => $request->email,
    		'password' => bcrypt($request->password),
    	]);

        $this->sendEmailConfirmationTo($user);

        session()->flash('success', '验证邮件已经发送到您的注册邮箱上，请注意查收。');

        return redirect('/');
    	/*Auth::login($user);

    	session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
    	
    	return redirect()->route('users.show',[$user]);*/
    }

    public function edit(User $user) {
        $this->authorize('update', $user);
        return view('users.edit',compact('user'));
    }

    public function update(User $user, Request $request) {
        $this->authorize('update', $user);
        
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        if($user->name != $request->name){
            $data['name'] = $request->name;
        }
        
        if($request->password && $user->password != bcrypt($request->password)){
            $data['password'] = bcrypt($request->password);
        }
        if(!empty($data)){
            $user->update($data);
            session()->flash('success', '个人资料更新成功！');    
        }else{
            session()->flash('success', '个人资料与原来相同！');    
        }
        
        return redirect()->route('users.show', $user->id);
    }

    public function index() {
        $users = User::paginate(10); 
        return view('users.index', compact('users'));
    }

    public function destroy(User $user) {
        
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '删除用户成功！');
        return back();
    }

    /**
     * 发送邮件
     * @param  [type] $user [description]
     * @return [type]       [description]
     */
    protected function sendEmailConfirmationTo($user) {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'xfchenju@163.com';
        $name = 'xfchenju'; 
        $to = $user->email;
        $subject = "感谢您注册 Sample 应用！请确认您的邮箱！";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    /**
     * 验证操作
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
    public function confirmEmail($token) {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true; 
        $user->activation_token = '';
        $user->save(); 

        Auth::login($user);
        session()->flash('success', '恭喜您，激活成功！'); 
        return redirect()->route('users.show', [$user]);
    }

    public function followings(User $user) {
        $users = $user->followings()->paginate(30);
        $title = '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    public function followers(User $user) {
        $users = $user->followers()->paginate(30);
        $title = '粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }
}
