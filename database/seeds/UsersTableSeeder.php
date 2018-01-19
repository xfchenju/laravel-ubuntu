<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(User::class)->times(50)->make();
        User::insert($users->makeVisible(['password', 'remember_token'])->toArray());

        $user = User::find(1);
        $user->name = '浮生未歇';
        $user->email  = '975982556@qq.com';
        $user->password  = bcrypt('xf2012?!');
        $user->is_admin = true;
        $user->activated = true;
        $user->save();
    }
}
