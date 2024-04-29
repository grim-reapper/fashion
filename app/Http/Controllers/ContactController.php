<?php

namespace App\Http\Controllers;

use App\Facades\SeoMeta;
use App\Http\Requests\StoreCallbackRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Feedback;
use App\Models\Widget;
use App\Repository\EmailRepository;
use App\Repository\FeedbackRepository;
use App\Repository\PagesRepository;
use FinalStrike\Email\Facades\EmailHandler;
use FinalStrike\Email\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{

    
    public function __construct() {
    
    }

    /**
     * Showing contact us page contents and form
     * @param  Request  $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        
        return view('pages.contact');
    }

    /**
     * Saves data of contact us form query
     * @param  StoreContactRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function contactSave(Request $request)
    {
        $request->validate(['name' => 'required', 'email' => 'required', 'message' => 'required']);
        
        $feedback = new Feedback();
        $feedback->user_name = $request['name'];
        $feedback->email = $request['email'];
        $feedback->message = $request['message'];
        $feedback->ip_address = request()->ip();
        $feedback->save();
        
        session()->flash('success', 'Thank you for contacting us');
        return redirect()->back();
    }

}
