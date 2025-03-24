<?php

namespace App\Http\Controllers;

use App\Models\CommandeProduit;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $products = Produit::all();
        $total = $products->count();
        $en_rupture = $products->filter(function (Produit $item, int $key) {return $item->enRupture();})->count();
        $perimes = CommandeProduit::whereNotNull('expirationDate')->where('expirationDate','<',now())->count();
        return Inertia::render('Dashboard', compact('total','en_rupture','perimes'));
    }
    public function notifications(){
      return Inertia::render('Dashboard/Notifications', ['items'=>Auth::user()->notifications]);
    }

    public function notificationDelete($notification_id){
        //Auth::user()->notifications()->find($notification_id)->delete();
        return redirect()->route('notifications.index')->banner('Notification supprimée.');
    }
    public function deleteAllNotifications(){
        Auth::user()->notifications()->foreach(function ($el){
            $el->delete();
        });
        return redirect()->route('notifications.index');
    }
    public function markAllAsRead(){
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back();
       // return redirect()->route('notifications.index');
    }
}
