<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\Request;
use Skoolyst\Core\Response;
use Skoolyst\Core\Validator;
use Skoolyst\Core\View;

/**
 * Static-ish frontend pages that don't warrant their own Service (no
 * persisted state beyond what's flashed back to the person).
 */
class PageController {
    public function about(): void {
        View::render('frontend/about', ['title' => 'About — Skoolyst Blog', 'description' => 'Learn about Skoolyst and this blog.', 'activeNav' => 'about'], 'frontend');
    }

    public function contact(): void {
        View::render('frontend/contact', ['title' => 'Contact — Skoolyst Blog', 'description' => 'Get in touch with the Skoolyst team.', 'activeNav' => 'contact'], 'frontend');
    }

    public function submitContact(): never {
        $errors = Validator::make(Request::all(), [
            'name' => 'required|max:120',
            'email' => 'required|email',
            'message' => 'required',
        ]);

        // No ticketing/email system is wired up yet — this just acknowledges
        // the submission. Wire this to a real inbox/notification in a later phase.
        flash($errors ? 'error' : 'success', $errors ? 'Please fill in all fields correctly.' : "Thanks for reaching out — we'll get back to you soon.");
        Response::redirect(url('/contact'));
    }

    public function newsletter(): never {
        $errors = Validator::make(Request::all(), ['email' => 'required|email']);
        // No subscriber list exists yet — acknowledges the signup only.
        flash($errors ? 'error' : 'success', $errors ? 'Enter a valid email address.' : "You're subscribed!");
        Response::redirect(url($_SERVER['HTTP_REFERER'] ?? '/'));
    }
}
