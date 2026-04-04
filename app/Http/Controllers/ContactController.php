<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUsMail;

class ContactController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        $html = view('emails.contact-us', ['contactData' => $validated])->render();
        (new \App\Services\BrevoService())->sendEmail(
            'teron@live.ca',
            'New Contact Inquiry: ' . $validated['subject'],
            $html
        );

        return back()->with('success', 'Message sent successfully!');
    }
}
