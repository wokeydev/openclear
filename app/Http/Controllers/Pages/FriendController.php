<?php

namespace App\Http\Controllers\Pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use App\User;
use App\Notifications\UserFollowed;

class FriendController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('pages.friend.index');
    }

    /**
     * Search friends.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function search()
    {
        $users = User::where('id', '!=', Auth::user()->id)->get();

        $data = [
            'users' => $users
        ];
        return view('pages.friend.search')->with($data);
    }

    /**
     * Add friend
     *
     * @return Response
     */
    public function addFriend(Request $request)
    {
        $userId = $request->input('user_id');

        $user = Auth::user();
        $recipient = User::find($userId);

        if ($user->canSendFriendRequest($recipient)) {
            $recipient->notify(new UserFollowed($user));

            $user->befriend($recipient);
            
            return response()->json([
                'message' => 'success',
            ]);
        }
        
        return response()->json([
            'message' => 'failed',
        ]);
    }

    /**
     * Get count of unread friend-request notifications
     *
     * @param Request $request
     * @return Response
     */
    public function getUnReadFollowCount(Request $request)
    {
        $user = Auth::user();
        $count = $user->unreadNotifications()
                    ->where('type', 'App\\Notifications\\UserFollowed')
                    ->get()
                    ->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Mark as read follow notification
     *
     * @param Request $request
     * @return Response
     */
    public function markAsReadFollowNotification(Request $request)
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications()->where('type', 'App\\Notifications\\UserFollowed')->get();

        foreach ($notifications as $notify) {
            $notify->markAsRead();
        }

        return response()->json([
            'message' => 'success',
        ]);
    }

    /**
     * Accept Friend
     *
     * @param Request $request
     * @return Response
     */
    public function acceptFriendRequest(Request $request)
    {
        $userId = $request->input('user_id');

        $user = Auth::user();
        $sender = User::find($userId);

        $user->acceptFriendRequest($sender);

        return response()->json([
            'message' => 'success',
        ]);
    }

    /**
     * Deny Friend
     *
     * @param Request $request
     * @return Response
     */
    public function denyFriendRequest(Request $request)
    {
        $userId = $request->input('user_id');

        $user = Auth::user();
        $sender = User::find($userId);

        $user->denyFriendRequest($sender);

        return response()->json([
            'message' => 'success',
        ]);
    }

    /**
     * Block Friend
     *
     * @param Request $request
     * @return Response
     */
    public function blockFriend(Request $request)
    {
        $userId = $request->input('user_id');

        $user = Auth::user();
        $sender = User::find($userId);

        $user->blockFriend($sender);

        return response()->json([
            'message' => 'success',
        ]);
    }

    /**
     * Remove Friend from Friendlists
     *
     * @param Request $request
     * @return Response
     */
    public function removeFriend(Request $request)
    {
        $userId = $request->input('user_id');

        $user = Auth::user();
        $friend = User::find($userId);

        $user->unfriend($friend);

        return response()->json([
            'message' => 'success',
        ]);
    }
}
