<?php

use Illuminate\Database\Seeder;
use App\Models\User;

class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {	
        $users = User::all(); 
        $user = $users->first(); 
        $user_id = $user->id; 

        //去除ID为1的所有用户ID数组
        $followers = $users->slice(1);
        $follower_ids = $followers->pluck('id')->toArray();

        //关注除了1号用户外的所有用户 
        $user->follow($follower_ids);

        foreach($followers as $follower) {
        	$follower->follow($user_id);
        }
    }
}
